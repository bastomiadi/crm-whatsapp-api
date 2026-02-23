<?php

namespace App\Services;

use App\Models\Contact;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DuplicateDetectionService
{
    /**
     * Find duplicate contacts by phone number.
     */
    public function findDuplicatePhones(): Collection
    {
        // Find phone numbers that appear more than once
        $duplicates = DB::table('contacts')
            ->select('phone', DB::raw('COUNT(*) as count'))
            ->whereNotNull('phone')
            ->groupBy('phone')
            ->having('count', '>', 1)
            ->get();

        $contacts = collect();

        foreach ($duplicates as $duplicate) {
            $contactGroup = Contact::where('phone', $duplicate->phone)->get();
            $contacts->push($contactGroup);
        }

        return $contacts;
    }

    /**
     * Find duplicate contacts by email.
     */
    public function findDuplicateEmails(): Collection
    {
        // Find emails that appear more than once
        $duplicates = DB::table('contacts')
            ->select('email', DB::raw('COUNT(*) as count'))
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->groupBy('email')
            ->having('count', '>', 1)
            ->get();

        $contacts = collect();

        foreach ($duplicates as $duplicate) {
            $contactGroup = Contact::where('email', $duplicate->email)->get();
            $contacts->push($contactGroup);
        }

        return $contacts;
    }

    /**
     * Find similar contacts by name (fuzzy matching).
     */
    public function findSimilarNames(float $similarity = 0.8): Collection
    {
        $contacts = Contact::whereNotNull('name')
            ->where('name', '!=', '')
            ->orderBy('name')
            ->get();

        $similarGroups = collect();
        $processed = [];

        foreach ($contacts as $contact) {
            if (in_array($contact->id, $processed)) continue;

            $similar = $contacts->filter(function ($other) use ($contact, $similarity) {
                if ($other->id === $contact->id) return false;
                if (!$other->name) return false;
                
                similar_text(
                    strtolower($contact->name), 
                    strtolower($other->name), 
                    $percent
                );
                
                return $percent >= ($similarity * 100);
            });

            if ($similar->isNotEmpty()) {
                $group = collect([$contact])->merge($similar);
                $similarGroups->push($group);
                
                foreach ($similar as $s) {
                    $processed[] = $s->id;
                }
            }
        }

        return $similarGroups;
    }

    /**
     * Merge duplicate contacts.
     */
    public function mergeContacts(int $keepId, array $mergeIds): Contact
    {
        $keepContact = Contact::findOrFail($keepId);
        
        // Get all contacts to merge
        $mergeContacts = Contact::whereIn('id', $mergeIds)->get();

        // Merge orders
        $orderIds = $mergeContacts->flatMap->orders->pluck('id')->toArray();
        \App\Models\Order::whereIn('id', $orderIds)->update(['contact_id' => $keepId]);

        // Merge tickets
        $ticketIds = $mergeContacts->flatMap->tickets->pluck('id')->toArray();
        \App\Models\Ticket::whereIn('id', $ticketIds)->update(['contact_id' => $keepId]);

        // Merge deals
        $dealIds = $mergeContacts->flatMap->deals->pluck('id')->toArray();
        \App\Models\Deal::whereIn('id', $dealIds)->update(['contact_id' => $keepId]);

        // Merge chatbot sessions
        $sessionIds = $mergeContacts->flatMap->chatbotSessions->pluck('id')->toArray();
        \App\Models\ChatbotSession::whereIn('id', $sessionIds)->update(['contact_id' => $keepId]);

        // Update contact info if empty
        if (!$keepContact->name) {
            $firstWithName = $mergeContacts->firstWhere('name', '!=', null);
            if ($firstWithName) {
                $keepContact->name = $firstWithName->name;
            }
        }

        if (!$keepContact->email) {
            $firstWithEmail = $mergeContacts->firstWhere('email', '!=', null);
            if ($firstWithEmail) {
                $keepContact->email = $firstWithEmail->email;
            }
        }

        if (!$keepContact->company) {
            $firstWithCompany = $mergeContacts->firstWhere('company', '!=', null);
            if ($firstWithCompany) {
                $keepContact->company = $firstWithCompany->company;
            }
        }

        if (!$keepContact->address) {
            $firstWithAddress = $mergeContacts->firstWhere('address', '!=', null);
            if ($firstWithAddress) {
                $keepContact->address = $firstWithAddress->address;
            }
        }

        // Merge tags
        $existingTags = $keepContact->tags ?? [];
        $newTags = $mergeContacts->pluck('tags')->filter()->flatten()->toArray();
        $keepContact->tags = array_unique(array_merge($existingTags, $newTags));

        $keepContact->save();

        // Delete merged contacts
        Contact::whereIn('id', $mergeIds)->delete();

        return $keepContact;
    }

    /**
     * Get duplicate statistics.
     */
    public function getStatistics(): array
    {
        $phoneDuplicates = $this->findDuplicatePhones()->flatten()->count();
        $emailDuplicates = $this->findDuplicateEmails()->flatten()->count();
        
        return [
            'total_phone_duplicates' => $phoneDuplicates,
            'total_email_duplicates' => $emailDuplicates,
            'duplicate_groups' => $this->findDuplicatePhones()->count() + $this->findDuplicateEmails()->count(),
        ];
    }
}
