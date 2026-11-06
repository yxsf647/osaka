<?php
/**
 * Osaka Wings Limited - Reception Display settings
 * Contact details and logo are taken from https://www.osakawings.co.ke
 */

$company = [
    'name'     => 'Osaka Wings Limited',
    'tagline'  => 'Premium Japanese Cars in Kenya',
    'phone'    => '+254 708 888 111',
    'whatsapp' => '254708888111',
    'email'    => 'info@osakawings.co.ke',
    'address'  => 'Ngong Road, Next to Kenya Meteorological Department, Nairobi',
    'website'  => 'www.osakawings.co.ke',
    'instagram'=> '@osakawings_ltd',
    // Full logo (circle + OSAKA WINGS wordmark): top-left corner of every slide.
    'logo_local'  => 'assets/logo.jpg',
    // Circle emblem only: used in the greeting animation.
    'logo_circle' => 'assets/logo-circle.png',
    'logo_remote' => 'https://www.osakawings.co.ke/uploads/logo/logo_1778431157.png',
];

/**
 * COLOURS
 * With 'match_logo' on, the display reads the logo and builds its palette from
 * the logo's main colour (this works best with the logo saved at assets/logo.png).
 * Sky blue and white theme. Turn 'match_logo' on to build the palette from the logo instead.
 */
$match_logo = false;
$colors = [
    'deep'   => '#0a6cb8', // deep sky blue (backgrounds)
    'mid'    => '#3db3f0', // light sky blue (gradients)
    'accent' => '#ffffff', // highlight colour (white)
];

// Seconds on screen
$seconds = [
    'greeting' => 9,
    'stats'    => 10,
    'car'      => 8,
    'info'     => 11,
];

// Numbers shown on the "Trusted by Kenyans" slide (from www.osakawings.co.ke)
$stats = [
    'satisfaction' => 98,   // percent
    'sold'         => 500,  // shown as 500+
];
$warranty = '3-Month Gearbox & Engine Warranty';

// Short lines that rotate in the bottom bar
$highlights = [
    'Pre-purchase inspection with Japanese auction grade reports',
    '3-month gearbox & engine warranty on every vehicle',
    'Free nationwide delivery across Kenya',
];
