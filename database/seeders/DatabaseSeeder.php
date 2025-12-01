<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\News;
use App\Models\TrackingInterest;
use App\Models\Shop;
use App\Models\WebDomain;
use App\Models\Page;
use App\Models\PageFound;
use App\Models\SearchQueryString;
use App\Models\Seller;
use App\Models\TicketInfo;
use App\Models\SQSSearchVolume;
use App\Models\Tenant;
use App\Models\TenantTrackingInterest;
use App\Models\UserTrackingInterest;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(TenantSeeder::class);

        $evolutionTenant = Tenant::where('is_system', true)->first();
        $otherTenants = Tenant::where('is_system', false)->get();

        $superadmin = User::factory()->superadmin()->create([
            'name' => 'Superadmin',
            'email' => 'superadmin@evolutiongroup.it',
            'tenant_id' => $evolutionTenant->id,
        ]);

        $tenantAdmin1 = User::factory()->tenantAdmin()->create([
            'name' => 'Tenant Admin 1',
            'email' => 'admin@tenant1.com',
            'tenant_id' => $otherTenants[0]->id,
        ]);

        $tenantAdmin2 = User::factory()->tenantAdmin()->create([
            'name' => 'Tenant Admin 2',
            'email' => 'admin@tenant2.com',
            'tenant_id' => $otherTenants[1]->id,
        ]);

        $user1 = User::factory()->create([
            'name' => 'User 1',
            'email' => 'user1@tenant1.com',
            'tenant_id' => $otherTenants[0]->id,
        ]);

        $user2 = User::factory()->create([
            'name' => 'User 2',
            'email' => 'user2@tenant1.com',
            'tenant_id' => $otherTenants[0]->id,
        ]);

        $ti = TrackingInterest::factory()->active()->create([
            'interest' => 'E-commerce Fashion',
        ]);

        $ti2 = TrackingInterest::factory()->active()->create([
            'interest' => 'Electronics Stores',
        ]);

        TenantTrackingInterest::assignToTenant($otherTenants[0], $ti, $superadmin);
        TenantTrackingInterest::assignToTenant($otherTenants[0], $ti2, $superadmin);
        TenantTrackingInterest::assignToTenant($otherTenants[1], $ti, $superadmin);

        UserTrackingInterest::assignToUser($user1, $ti, $tenantAdmin1);
        UserTrackingInterest::assignToUser($user2, $ti2, $tenantAdmin1);

        $webDomains = WebDomain::factory()->count(15)->create();

        $sellers = Seller::factory()->count(8)->create()->each(function ($seller) use ($webDomains) {
            $seller->found_on_domain_id = $webDomains->random()->id;
            $seller->save();
        });

        $shops1 = Shop::factory()->count(5)->create()->each(function ($shop) use ($webDomains) {
            $domainsToAttach = $webDomains->random(rand(1, 3));
            $shop->webDomains()->attach($domainsToAttach->pluck('id'));
        });

        $shops2 = Shop::factory()->count(3)->create()->each(function ($shop) use ($webDomains) {
            $domainsToAttach = $webDomains->random(rand(1, 2));
            $shop->webDomains()->attach($domainsToAttach->pluck('id'));
        });

        $searchQueries1 = SearchQueryString::factory()->count(5)->forTrackingInterest($ti)->italian()->create();
        $searchQueries2 = SearchQueryString::factory()->count(5)->forTrackingInterest($ti2)->italian()->create();

        foreach ($shops1 as $shop) {
            $pages = Page::factory()->count(rand(2, 4))->forShop($shop)->create()->each(function ($page) use ($sellers) {
                if (rand(1, 10) <= 7) {
                    $page->seller_id = $sellers->random()->id;
                    $page->save();
                }
            });

            foreach ($pages as $page) {
                $searchQuery = $searchQueries1->random();
                PageFound::factory()
                    ->forPage($page)
                    ->forTrackingInterest($ti)
                    ->withSearchQuery($searchQuery)
                    ->create();
            }
        }

        foreach ($shops2 as $shop) {
            $pages = Page::factory()->count(rand(2, 4))->forShop($shop)->create()->each(function ($page) use ($sellers) {
                if (rand(1, 10) <= 7) {
                    $page->seller_id = $sellers->random()->id;
                    $page->save();
                }
            });

            foreach ($pages as $page) {
                $searchQuery = $searchQueries2->random();
                PageFound::factory()
                    ->forPage($page)
                    ->forTrackingInterest($ti2)
                    ->withSearchQuery($searchQuery)
                    ->create();
            }
        }

        News::factory()->count(3)->create([
            'for_user_id' => $superadmin->id,
            'added_by_user_id' => $tenantAdmin1->id,
        ]);

        News::factory()->count(2)->create([
            'for_tenant_id' => $otherTenants[0]->id,
            'added_by_user_id' => $superadmin->id,
        ]);

        News::factory()->short()->count(2)->create([
            'for_user_id' => $user1->id,
            'added_by_user_id' => $tenantAdmin1->id,
        ]);

        News::factory()->withText('Sistema aggiornato con nuove funzionalità di tracking')->create([
            'for_user_id' => $superadmin->id,
            'added_by_user_id' => $tenantAdmin1->id,
        ]);

        News::factory()->withText('Nuovo report mensile disponibile nella dashboard')->create([
            'for_tenant_id' => $otherTenants[0]->id,
            'added_by_user_id' => $superadmin->id,
        ]);

        News::factory()->withText('Nuovi risultati trovati per E-commerce Fashion')->create([
            'for_tracking_interest_id' => $ti->id,
            'added_by_user_id' => $superadmin->id,
        ]);

        $allPages = Page::all();
        $pagesWithTickets = $allPages->random($allPages->count() * 0.6);

        foreach ($pagesWithTickets as $page) {
            $ticketCount = rand(1, 3);
            TicketInfo::factory()->count($ticketCount)->forPage($page)->create();

            if (rand(1, 10) <= 3) {
                TicketInfo::factory()->concert()->expensive()->forPage($page)->create();
            }

            if (rand(1, 10) <= 2) {
                TicketInfo::factory()->sport()->cheap()->withCurrency('EUR')->forPage($page)->create();
            }
        }

        TicketInfo::factory()->withCurrency('EUR')->create([
            'page_id' => $allPages->random()->id,
            'ticket_type' => 'festival',
            'selling_price' => 85.50,
            'description' => 'Festival della Musica Italiana - 3 giorni',
        ]);

        $allSearchQueries = SearchQueryString::all();

        foreach ($allSearchQueries as $searchQuery) {
            $volumeCount = rand(1, 4);
            SQSSearchVolume::factory()->count($volumeCount)->forSearchQuery($searchQuery)->create();

            if (rand(1, 10) <= 4) {
                SQSSearchVolume::factory()->highVolume()->recent()->fromSource('Google Ads')->forSearchQuery($searchQuery)->create();
            }

            if (rand(1, 10) <= 3) {
                SQSSearchVolume::factory()->monthlyData()->forSearchQuery($searchQuery)->create();
            }
        }

        SQSSearchVolume::factory()->create([
            'search_query_string_id' => $searchQueries1->random()->id,
            'volume' => 25000,
            'data_source' => 'SEMrush',
            'description' => 'Picco stagionale per fashion e-commerce',
        ]);
    }
}