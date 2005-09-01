Pacifica Co-Op Nursery School Data Entry

NOTE! THIS IS THE DEV SITE! THIS IS NOT LIVE DATA, THIS IS ONLY FOR TESTING.
If you want the live site, use the "members" link, NOT "members-dev". Thanks.

08/31/2005

DEBUG DEAL WITH duplicate realmmap
Array
(
    [auction_donation_items] => Array
        (
            [auction] => 5
            [packaging] => 9
            [solicitation] => 12
        )

    [income] => Array
        (
            [invitations_cash] => 6
            [money] => 28
            [solicit_money] => 13
            [raffle] => 22
        )

    [ads] => Array
        (
            [program] => 10
            [solicitation] => 15
        )

    [companies] => Array
        (
            [solicitation] => 11
            [flyers] => 23
        )

)

DEBUG DEAL WITH duplicate pagemap
Array
(
    [enhancement_hours] => Array
        (
            [enhancement_hours.php] => 2
            [enhancement_summary.php] => 3
        )

    [auction_donation_items] => Array
        (
            [auction.php] => 5
            [packaging_checkin.php] => 9
            [solicit_auction.php] => 12
        )

    [income] => Array
        (
            [invitation_cash.php] => 6
            [money.php] => 7
            [solicit_cash.php] => 13
            [raffle_finance.php] => 22
            [reconciliation.php] => 27
            [carriereport.php] => 28
        )

    [ads] => Array
        (
            [program_ads.php] => 10
            [solicit_ads.php] => 15
        )

    [companies] => Array
        (
            [solicit_company.php] => 11
            [flyer_company.php] => 23
        )

)


///================ enrollment ======================


// 1)----- enrollment (realm: roster) -----

var $fb_usePage = 'newroster.php';

var $fb_shortHeader = 'Roster';

var $fb_requiredFields = array(
   'kid_id',
   'am_pm_session',
   'school_year'
);

var $fb_SAVEALWAYS = array(
   'enrollment_id'
);

///================ enhancement_projects ======================


// 2)----- enhancement_projects (realm: enhancement) -----

var $fb_usePage = 'enhancement_projects.php';

var $fb_shortHeader = 'Projects';

var $fb_requiredFields = array(
   'project_name'
);

var $fb_SAVEALWAYS = array(
   'enhancement_project_id'
);

// set project_description size = 100

// set project_description lines = 3

///================ enhancement_hours ======================


// 3)----- enhancement_hours (realm: enhancement) -----

var $fb_usePage = 'enhancement_hours.php';

var $fb_shortHeader = 'Hours';

var $fb_requiredFields = array(
   'enhancement_project_id',
   'hours',
   'work_date',
   'school_year',
   'parent_id'
);

var $fb_joinPaths = array(
  'school_year' => 'enrollment'
);

var $fb_SAVEALWAYS = array(
   'enhancement_hour_id'
);

// set hours size = 10


// 4)----- enhancement_hours (realm: enhancement) -----

var $fb_usePage = 'enhancement_summary.php';

var $fb_shortHeader = 'Status';

var $fb_requiredFields = array(
   'semester'
);

var $fb_dupeIgnore = array(
   'family_id'
);

var $fb_SAVEALWAYS = array(
   'family_id'
);

///================ leads ======================


// 5)----- leads (realm: invitations) -----

var $fb_usePage = '10names.php';

var $fb_allYears = 1;

var $fb_shortHeader = 'Contacts';

var $fb_dupeIgnore = array(
   'family_id',
   'salutation',
   'title',
   'address2',
   'relation'
);

var $fb_SAVEALWAYS = array(
   'family_id',
   'do_not_contact',
   'source_id',
   'lead_id'
);

var $fb_joinPaths = array(
  'school_year' => 'enrollment'
);

var $fb_requiredFields = array(
   'last_name',
   'address1',
   'city',
   'state',
   'zip',
   'country',
   'relation',
   'school_year'
);

var $fb_defaults = array(
  'city' => 'Pacifica',
  'state' => 'CA',
  'zip' => 94044,
  'country' => 'USA',
  'source_id' => 1
);

// set salutation len = 25

// set salutation size = 20

// set first_name size = 50

// set address1 size = 50

// set city size = 15

// set state size = 5

// set zip size = 8

// set country size = 10

///================ auction_donation_items ======================


// 6)----- auction_donation_items (realm: auction) -----

var $fb_usePage = 'auction.php';

var $fb_shortHeader = 'Donation Items';

var $fb_requiredFields = array(
   'family_id',
   'quantity',
   'item_description',
   'item_value',
   'item_type',
   'school_year'
);

var $fb_SAVEALWAYS = array(
   'family_id',
   'auction_donation_item_id'
);

var $fb_joinPaths = array(
  'school_year' => 'enrollment'
);

var $fb_defaults = array(
  'quantity' => 1
);

var $fb_dupeIgnore = array(
   'item_value',
   'date_received'
);

var $fb_currencyFields = array(
   'item_value'
);

// set item_description size = 100

// set item_description lines = 3


// 10)----- auction_donation_items (realm: packaging) -----

var $fb_usePage = 'packaging_checkin.php';

var $fb_shortHeader = 'Inventory';


// 13)----- auction_donation_items (realm: solicitation) -----

var $fb_usePage = 'solicit_auction.php';

var $fb_shortHeader = ' Auction Donations';

var $fb_requiredFields = array(
   'company_id',
   'quantity',
   'item_description',
   'item_type',
   'item_value',
   'family_id',
   'school_year'
);

var $fb_defaults = array(
  'quantity' => 1
);

var $fb_currencyFields = array(
   'item_value'
);

var $fb_joinPaths = array(
  'school_year' => 'enrollment'
);

var $fb_SAVEALWAYS = array(
   'auction_donation_item_id'
);

// set item_description size = 100

// set item_description lines = 3

///================ income ======================


// 7)----- income (realm: invitations_cash) -----

var $fb_usePage = 'invitation_cash.php';

var $fb_shortHeader = 'RSVPs';


// 8)----- income (realm: money) -----

var $fb_usePage = 'money.php';

var $fb_shortHeader = ' Per-Family Fees';

var $fb_requiredFields = array(
   'family_id',
   'check_date',
   'payer',
   'payment_amount',
   'account_number',
   'school_year'
);

var $fb_joinPaths = array(
  'school_year' => 'enrollment'
);

var $fb_currencyFields = array(
   'payment_amount'
);

var $fb_dupeIgnore = array(
   'note'
);

var $fb_SAVEALWAYS = array(
   'income_id'
);

// set check_number size = 10

// set account_number check_jointo = families


// 14)----- income (realm: solicit_money) -----

var $fb_usePage = 'solicit_cash.php';

var $fb_shortHeader = 'Income';

var $fb_requiredFields = array(
   'company_id',
   'check_number',
   'check_date',
   'payer',
   'payment_amount',
   'account_number',
   'school_year'
);

var $fb_currencyFields = array(
   'payment_amount'
);

var $fb_dupeIgnore = array(
   'note'
);

var $fb_joinPaths = array(
  'school_year' => 'enrollment'
);

var $fb_SAVEALWAYS = array(
   'income_id'
);

// set check_number size = 10

// set account_number check_jointo = companies


// 23)----- income (realm: raffle) -----

var $fb_usePage = 'raffle_finance.php';

var $fb_shortHeader = 'Raffle Income';

var $fb_requiredFields = array(
   'raffle_location_id',
   'payer',
   'payment_amount',
   'bookkeeper_date',
   'account_number',
   'school_year'
);

var $fb_defaults = array(
  'check_number' => 'CASH',
  'payer' => 'CASH',
  'account_number' => 8
);

var $fb_currencyFields = array(
   'payment_amount'
);

var $fb_dupeIgnore = array(
   'note'
);

var $fb_joinPaths = array(
  'school_year' => 'enrollment'
);

var $fb_SAVEALWAYS = array(
   'income_id'
);

// set check_number size = 10


// 28)----- income (realm: money) -----

var $fb_usePage = 'reconciliation.php';

var $fb_shortHeader = 'Reconciliation';

var $fb_requiredFields = array(
   'check_date',
   'payer',
   'payment_amount',
   'school_year'
);

var $fb_currencyFields = array(
   'payment_amount'
);

var $fb_SAVEALWAYS = array(
   'account_number',
   'income_id'
);

var $fb_dupeIgnore = array(
   'note'
);

var $fb_joinPaths = array(
  'school_year' => 'enrollment'
);

// set check_number size = 10


// 29)----- income (realm: money) -----

var $fb_usePage = 'carriereport.php';

var $fb_shortHeader = 'Program Performance';

var $fb_requiredFields = array(
   'school_year'
);

///================ packages ======================


// 9)----- packages (realm: packaging) -----

var $fb_usePage = 'packages.php';

var $fb_shortHeader = 'Packages';

var $fb_dupeIgnore = array(
   'package_type',
   'package_title',
   'package_description',
   'donated_by_text',
   'item_type',
   'package_value',
   'starting_bid',
   'bid_increment'
);

var $fb_requiredFields = array(
   'package_type',
   'package_number',
   'item_type',
   'school_year'
);

var $fb_defaults = array(
  'package_type' => 'Silent'
);

var $fb_currencyFields = array(
   'package_value',
   'starting_bid',
   'bid_increment'
);

var $fb_SAVEALWAYS = array(
   'package_id'
);

// set package_description size = 100

// set package_description lines = 3

///================ ads ======================


// 11)----- ads (realm: program) -----

var $fb_usePage = 'program_ads.php';

var $fb_shortHeader = 'Ad Status';

var $fb_requiredFields = array(
   'company_id',
   'ad_size_id',
   'artwork_provided',
   'family_id',
   'school_year'
);

var $fb_dupeIgnore = array(
   'artwork_provided',
   'ad_copy'
);

var $fb_defaults = array(
  'artwork_provided' => 'Yes'
);

var $fb_SAVEALWAYS = array(
   'family_id',
   'ad_id'
);

var $fb_joinPaths = array(
  'school_year' => 'enrollment'
);

// set ad_copy size = 100

// set ad_copy lines = 3


// 16)----- ads (realm: solicitation) -----

var $fb_usePage = 'solicit_ads.php';

var $fb_shortHeader = 'Ad Sales';

var $fb_requiredFields = array(
   'company_id',
   'ad_size_id',
   'artwork_provided',
   'family_id',
   'school_year'
);

var $fb_dupeIgnore = array(
   'artwork_provided',
   'ad_copy'
);

var $fb_defaults = array(
  'artwork_provided' => 'Yes'
);

var $fb_SAVEALWAYS = array(
   'family_id',
   'ad_id'
);

var $fb_joinPaths = array(
  'school_year' => 'enrollment'
);

// set ad_copy size = 100

// set ad_copy lines = 3

///================ companies ======================


// 12)----- companies (realm: solicitation) -----

var $fb_usePage = 'solicit_company.php';

var $fb_shortHeader = 'Contacts';

var $fb_requiredFields = array(
   'company_name',
   'state',
   'country'
);

var $fb_dupeIgnore = array(
   'family_id',
   'salutation',
   'title',
   'address2',
   'phone',
   'fax',
   'email_address',
   'url',
   'territory_id',
   'flyer_ok'
);

var $fb_defaults = array(
  'city' => 'Pacifica',
  'state' => 'CA',
  'zip' => 94044,
  'country' => 'USA',
  'flyer_ok' => 'Unknown'
);

var $fb_SAVEALWAYS = array(
   'company_id'
);

// set salutation len = 25

// set salutation size = 20

// set address1 size = 50

// set city size = 15

// set state size = 5

// set zip size = 8

// set country size = 10


// 24)----- companies (realm: flyers) -----

var $fb_usePage = 'flyer_company.php';

var $fb_shortHeader = 'Contacts';

var $fb_requiredFields = array(
   'company_name',
   'state',
   'country'
);

var $fb_dupeIgnore = array(
   'family_id',
   'salutation',
   'title',
   'address2',
   'phone',
   'fax',
   'email_address',
   'url',
   'territory_id',
   'flyer_ok'
);

var $fb_defaults = array(
  'city' => 'Pacifica',
  'state' => 'CA',
  'zip' => 94044,
  'country' => 'USA',
  'flyer_ok' => 'Yes'
);

var $fb_SAVEALWAYS = array(
   'company_id'
);

// set salutation len = 25

// set salutation size = 20

// set address1 size = 50

// set city size = 15

// set state size = 5

// set zip size = 8

// set country size = 10

///================ solicitation_calls ======================


// 15)----- solicitation_calls (realm: solicitation) -----

var $fb_usePage = 'solicit_calls.php';

var $fb_shortHeader = 'Misc. Notes';

var $fb_requiredFields = array(
   'company_id',
   'method_of_contact',
   'family_id',
   'done',
   'school_year'
);

var $fb_SAVEALWAYS = array(
   'family_id',
   'solicitation_call_id'
);

var $fb_joinPaths = array(
  'school_year' => 'enrollment'
);

// set call_note size = 100

// set call_note lines = 3

///================ in_kind_donations ======================


// 17)----- in_kind_donations (realm: solicitation) -----

var $fb_usePage = 'solicit_in_kind.php';

var $fb_shortHeader = 'In-kind Donations';

var $fb_requiredFields = array(
   'company_id',
   'quantity',
   'item_description',
   'item_value',
   'family_id',
   'school_year'
);

var $fb_defaults = array(
  'quantity' => 1
);

var $fb_currencyFields = array(
   'item_value'
);

var $fb_joinPaths = array(
  'school_year' => 'enrollment'
);

var $fb_SAVEALWAYS = array(
   'in_kind_donation_id'
);

// set item_description size = 100

// set item_description lines = 3

///================ ======================


// 18)----- (realm: solicitation) -----

var $fb_usePage = 'solicit_summary.php';

var $fb_shortHeader = 'Summary';

var $fb_requiredFields = array(
   'school_year'
);

///================ thank_you ======================


// 19)----- thank_you (realm: thankyou) -----

var $fb_usePage = 'thank_you_notes.php';

var $fb_shortHeader = 'Thank-You Notes';

var $fb_dupeIgnore = array(
   'method'
);

var $fb_requiredFields = array(
   'method'
);

var $fb_joinPaths = array(
  'school_year' => 'enrollment'
);

var $fb_SAVEALWAYS = array(
   'thank_you_id'
);

///================ tickets ======================


// 20)----- tickets (realm: tickets) -----

var $fb_usePage = 'ticket_sales.php';

var $fb_shortHeader = 'Reservations';

var $fb_requiredFields = array(
   'ticket_id'
);

///================ springfest_attendees ======================


// 21)----- springfest_attendees (realm: tickets) -----

var $fb_usePage = 'paddles.php';

var $fb_shortHeader = 'Paddles';

var $fb_requiredFields = array(
   'springfest_attendee_id',
   'school_year'
);

///================ raffle_locations ======================


// 22)----- raffle_locations (realm: raffle) -----

var $fb_usePage = 'raffle_locations.php';

var $fb_shortHeader = 'Locations';

var $fb_requiredFields = array(
   'location_name'
);

var $fb_SAVEALWAYS = array(
   'raffle_location_id'
);

///================ flyer_deliveries ======================


// 25)----- flyer_deliveries (realm: flyers) -----

var $fb_usePage = 'flyer_delivery.php';

var $fb_shortHeader = 'Deliveries';

var $fb_requiredFields = array(
   'company_id',
   'delivered_date',
   'family_id',
   'school_year'
);

var $fb_joinPaths = array(
  'school_year' => 'enrollment'
);

var $fb_SAVEALWAYS = array(
   'flyer_delivery_id'
);

///================ families ======================


// 26)----- families (realm: nag) -----

var $fb_usePage = 'nag.php';

var $fb_shortHeader = 'Summary';

///================ nag_indulgences ======================


// 27)----- nag_indulgences (realm: nag) -----

var $fb_usePage = 'indulgences.php';

var $fb_shortHeader = 'Indulgences';

var $fb_requiredFields = array(
   'family_id',
   'indulgence_type',
   'granted_date',
   'school_year'
);

var $fb_joinPaths = array(
  'school_year' => 'enrollment'
);

var $fb_SAVEALWAYS = array(
   'nag_indulgence_id'
);

// total 29 found

-----------------------------------------------------------------------------------

Valid XHTML 1.0! If anything on this site appears to be not working properly, email
ken@restivo.org or call ken at 650-355-1317

