<?php

namespace App\Services;

use App\Models\Contact;
use App\Models\Order;
use App\Models\Deal;
use App\Models\Product;
use App\Models\Ticket;
use App\Models\Campaign;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ExportService
{
    /**
     * Export contacts to CSV/Excel.
     */
    public function exportContacts(Collection $contacts): array
    {
        $data = $contacts->map(function ($contact) {
            return [
                'ID' => $contact->id,
                'Phone' => $contact->phone,
                'Name' => $contact->name,
                'Email' => $contact->email,
                'Company' => $contact->company,
                'Address' => $contact->address,
                'Status' => $contact->status,
                'Segment' => $contact->segment?->name,
                'Tags' => implode(', ', $contact->tags ?? []),
                'Total Orders' => $contact->orders->count(),
                'Total Spent' => $contact->total_spent,
                'Last Contacted' => $contact->last_contacted_at?->format('Y-m-d H:i'),
                'Created At' => $contact->created_at->format('Y-m-d H:i'),
            ];
        });

        return $data->toArray();
    }

    /**
     * Export orders to CSV/Excel.
     */
    public function exportOrders(Collection $orders): array
    {
        $data = $orders->map(function ($order) {
            return [
                'ID' => $order->id,
                'Order Number' => $order->order_number,
                'Contact Name' => $order->contact?->name,
                'Contact Phone' => $order->contact?->phone,
                'Status' => $order->status,
                'Total Amount' => $order->total_amount,
                'Currency' => $order->currency,
                'Shipping Address' => $order->shipping_address,
                'Tracking Number' => $order->tracking_number,
                'Ordered At' => $order->ordered_at?->format('Y-m-d H:i'),
                'Created At' => $order->created_at->format('Y-m-d H:i'),
            ];
        });

        return $data->toArray();
    }

    /**
     * Export deals to CSV/Excel.
     */
    public function exportDeals(Collection $deals): array
    {
        $data = $deals->map(function ($deal) {
            return [
                'ID' => $deal->id,
                'Title' => $deal->title,
                'Contact Name' => $deal->contact?->name,
                'Contact Phone' => $deal->contact?->phone,
                'Assigned To' => $deal->owner?->name,
                'Stage' => $deal->stage,
                'Value' => $deal->value,
                'Currency' => $deal->currency,
                'Probability' => $deal->probability . '%',
                'Source' => $deal->source,
                'Expected Close Date' => $deal->expected_close_date?->format('Y-m-d'),
                'Actual Close Date' => $deal->actual_close_date?->format('Y-m-d'),
                'Created At' => $deal->created_at->format('Y-m-d H:i'),
            ];
        });

        return $data->toArray();
    }

    /**
     * Export products to CSV/Excel.
     */
    public function exportProducts(Collection $products): array
    {
        $data = $products->map(function ($product) {
            return [
                'ID' => $product->id,
                'SKU' => $product->sku,
                'Name' => $product->name,
                'Description' => $product->description,
                'Price' => $product->price,
                'Stock' => $product->stock,
                'Category' => $product->category?->name,
                'Status' => $product->status,
                'Created At' => $product->created_at->format('Y-m-d H:i'),
            ];
        });

        return $data->toArray();
    }

    /**
     * Export tickets to CSV/Excel.
     */
    public function exportTickets(Collection $tickets): array
    {
        $data = $tickets->map(function ($ticket) {
            return [
                'ID' => $ticket->id,
                'Ticket Number' => $ticket->ticket_number,
                'Subject' => $ticket->subject,
                'Contact Name' => $ticket->contact?->name,
                'Contact Phone' => $ticket->contact?->phone,
                'Assigned To' => $ticket->assignedTo?->name,
                'Priority' => $ticket->priority,
                'Status' => $ticket->status,
                'Created At' => $ticket->created_at->format('Y-m-d H:i'),
                'Closed At' => $ticket->closed_at?->format('Y-m-d H:i'),
            ];
        });

        return $data->toArray();
    }

    /**
     * Generate export filename.
     */
    public function generateFilename(string $type): string
    {
        return $type . '_export_' . now()->format('Y_m_d_H_i_s');
    }
}
