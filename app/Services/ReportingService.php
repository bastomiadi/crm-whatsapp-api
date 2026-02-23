<?php

namespace App\Services;

use App\Models\Contact;
use App\Models\Order;
use App\Models\Deal;
use App\Models\Campaign;
use App\Models\Ticket;
use App\Models\ChatMessage;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportingService
{
    /**
     * Get dashboard overview statistics.
     */
    public function getDashboardStats(): array
    {
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();
        
        return [
            'contacts' => [
                'total' => Contact::count(),
                'new_today' => Contact::whereDate('created_at', $today)->count(),
                'new_this_month' => Contact::whereDate('created_at', '>=', $thisMonth)->count(),
            ],
            'orders' => [
                'total' => Order::count(),
                'total_amount' => Order::sum('total_amount'),
                'pending' => Order::where('status', 'pending')->count(),
                'this_month' => Order::whereDate('ordered_at', '>=', $thisMonth)->count(),
            ],
            'deals' => [
                'total' => Deal::count(),
                'total_value' => Deal::sum('value'),
                'won' => Deal::where('stage', 'closed_won')->count(),
                'won_value' => Deal::where('stage', 'closed_won')->sum('value'),
            ],
            'tickets' => [
                'total' => Ticket::count(),
                'open' => Ticket::whereIn('status', ['open', 'in_progress'])->count(),
                'avg_response_time' => $this->getAverageTicketResponseTime(),
            ],
            'messages' => [
                'sent_today' => ChatMessage::where('direction', 'outbound')
                    ->whereDate('created_at', $today)->count(),
                'received_today' => ChatMessage::where('direction', 'inbound')
                    ->whereDate('created_at', $today)->count(),
            ],
        ];
    }

    /**
     * Get sales performance report.
     */
    public function getSalesReport(array $filters = []): array
    {
        $query = Order::query()->with('contact');

        if (isset($filters['start_date'])) {
            $query->whereDate('ordered_at', '>=', $filters['start_date']);
        }

        if (isset($filters['end_date'])) {
            $query->whereDate('ordered_at', '<=', $filters['end_date']);
        }

        $orders = $query->get();

        return [
            'total_orders' => $orders->count(),
            'total_revenue' => $orders->sum('total_amount'),
            'average_order_value' => $orders->avg('total_amount'),
            'orders_by_status' => $orders->groupBy('status')->map->count(),
            'orders_by_day' => $orders->groupBy(function ($order) {
                return $order->ordered_at?->format('Y-m-d');
            })->map->count(),
            'top_customers' => $orders->groupBy('contact_id')
                ->map(function ($group) {
                    return [
                        'contact' => $group->first()->contact,
                        'order_count' => $group->count(),
                        'total_spent' => $group->sum('total_amount'),
                    ];
                })->sortByDesc('total_spent')->take(10)->values(),
        ];
    }

    /**
     * Get pipeline report.
     */
    public function getPipelineReport(): array
    {
        $deals = Deal::with('contact', 'owner')->get();

        return [
            'total_deals' => $deals->count(),
            'total_value' => $deals->sum('value'),
            'by_stage' => $deals->groupBy('stage')->map(function ($group) {
                return [
                    'count' => $group->count(),
                    'value' => $group->sum('value'),
                    'avg_value' => $group->avg('value'),
                ];
            }),
            'by_owner' => $deals->groupBy('assigned_to')->map(function ($group) {
                return [
                    'count' => $group->count(),
                    'value' => $group->sum('value'),
                    'won' => $group->where('stage', 'closed_won')->count(),
                ];
            }),
            'avg_time_to_close' => $this->getAverageDealCloseTime(),
            'win_rate' => $this->calculateWinRate(),
        ];
    }

    /**
     * Get campaign performance report.
     */
    public function getCampaignReport(): array
    {
        $campaigns = Campaign::with('contact')->get();

        return [
            'total_campaigns' => $campaigns->count(),
            'total_recipients' => $campaigns->sum(function ($c) {
                return is_array($c->recipients) ? count($c->recipients) : 0;
            }),
            'by_status' => $campaigns->groupBy('status')->map->count(),
            'by_type' => $campaigns->groupBy('type')->map->count(),
        ];
    }

    /**
     * Get contact analytics.
     */
    public function getContactAnalytics(): array
    {
        $contacts = Contact::with('orders', 'tickets', 'deals')->get();

        return [
            'total_contacts' => $contacts->count(),
            'by_status' => $contacts->groupBy('status')->map->count(),
            'by_segment' => $contacts->groupBy('segment_id')->map->count(),
            'with_orders' => $contacts->filter->orders->count(),
            'with_tickets' => $contacts->filter->tickets->count(),
            'with_deals' => $contacts->filter->deals->count(),
            'total_customers' => $contacts->filter(function ($c) {
                return $c->orders->count() > 0;
            })->count(),
        ];
    }

    /**
     * Calculate average ticket response time.
     */
    private function getAverageTicketResponseTime(): ?float
    {
        $tickets = Ticket::whereNotNull('assigned_to')
            ->whereNotNull('first_response_at')
            ->get();

        if ($tickets->isEmpty()) return null;

        $totalMinutes = $tickets->sum(function ($ticket) {
            return $ticket->created_at->diffInMinutes($ticket->first_response_at);
        });

        return round($totalMinutes / $tickets->count(), 1);
    }

    /**
     * Calculate average deal close time.
     */
    private function getAverageDealCloseTime(): ?int
    {
        $closedDeals = Deal::whereNotNull('actual_close_date')
            ->whereNotNull('created_at')
            ->get();

        if ($closedDeals->isEmpty()) return null;

        $totalDays = $closedDeals->sum(function ($deal) {
            return $deal->created_at->diffInDays($deal->actual_close_date);
        });

        return round($totalDays / $closedDeals->count());
    }

    /**
     * Calculate win rate.
     */
    private function calculateWinRate(): float
    {
        $closedDeals = Deal::whereIn('stage', ['closed_won', 'closed_lost'])->count();
        
        if ($closedDeals === 0) return 0;

        $wonDeals = Deal::where('stage', 'closed_won')->count();
        
        return round(($wonDeals / $closedDeals) * 100, 1);
    }
}
