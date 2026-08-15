<?php
/**
 * ElectroStore — Dynamic Product Image Generator
 * Returns a beautiful SVG illustration for any product category.
 * NO PHP inside heredoc strings — all shapes are pure SVG.
 * Usage: product_img.php?cat=smartphones&name=Samsung+S24&v=0
 */
header('Content-Type: image/svg+xml');
header('Cache-Control: public, max-age=604800');
header('X-Content-Type-Options: nosniff');

$cat     = strtolower(preg_replace('/[^a-z0-9\-]/', '', $_GET['cat'] ?? 'electronics'));
$name    = htmlspecialchars(mb_strimwidth(strip_tags($_GET['name'] ?? ''), 0, 38, '…'), ENT_XML1);
$variant = abs((int)($_GET['v'] ?? 0)) % 4;

/* ── Colour palettes per category ─────────────────────────── */
$themes = [
  'smartphones'     => [['#0d1b2a','#1b4f72'],['#0a0a0a','#1a237e'],['#1a0533','#4a148c'],['#0d2b0d','#1b5e20']],
  'laptops'         => [['#0d1b2a','#154360'],['#1a1a2e','#16213e'],['#0f2027','#203a43'],['#1c1c1c','#2d3561']],
  'tablets'         => [['#1a0533','#6a1b9a'],['#1a237e','#283593'],['#0d1b2a','#1565c0'],['#004d40','#00695c']],
  'smartwatches'    => [['#1c0a00','#7f3700'],['#0a0a0a','#bf360c'],['#1a237e','#0d47a1'],['#1b1f3a','#283593']],
  'desktops'        => [['#0d1b2a','#1b4f72'],['#1a1a2e','#16213e'],['#0f0f0f','#1a237e'],['#0d2b0d','#1b5e20']],
  'printers'        => [['#1a237e','#283593'],['#006064','#00838f'],['#1b0020','#4a0072'],['#0d1b2a','#1b4f72']],
  'cameras'         => [['#1c0a00','#4e342e'],['#1a1a2e','#37474f'],['#0f2027','#2c5364'],['#0a0a0a','#263238']],
  'gaming'          => [['#3b0000','#b71c1c'],['#1a0a30','#4a148c'],['#0d0d0d','#1a237e'],['#1a0a00','#e65100']],
  'networking'      => [['#0d3349','#0277bd'],['#004d40','#00695c'],['#1a237e','#1565c0'],['#0d1b2a','#154360']],
  'audio'           => [['#1a0030','#6a1b9a'],['#1c0a00','#e65100'],['#1a0a30','#880e4f'],['#0a0f1e','#1a237e']],
  'accessories'     => [['#1b1f3a','#283593'],['#004d40','#00695c'],['#1c0a00','#4e342e'],['#0d1b2a','#154360']],
  'gaming-laptops'  => [['#3b0000','#b71c1c'],['#1a0a30','#4a148c'],['#0d0d0d','#1a237e'],['#1a0a00','#e65100']],
  'business-laptops'=> [['#0d1b2a','#154360'],['#1a1a2e','#16213e'],['#0f2027','#203a43'],['#1c1c1c','#2d3561']],
];

$palette  = $themes[$cat][$variant] ?? $themes['accessories'][$variant];
[$dark, $light] = $palette;

/* ── Category label ────────────────────────────────────────── */
$labels = [
  'smartphones'      => 'SMARTPHONE',
  'laptops'          => 'LAPTOP',
  'tablets'          => 'TABLET',
  'smartwatches'     => 'SMARTWATCH',
  'desktops'         => 'DESKTOP PC',
  'printers'         => 'PRINTER',
  'cameras'          => 'CAMERA',
  'gaming'           => 'GAMING',
  'networking'       => 'NETWORKING',
  'audio'            => 'AUDIO',
  'accessories'      => 'ACCESSORIES',
  'gaming-laptops'   => 'GAMING LAPTOP',
  'business-laptops' => 'BUSINESS LAPTOP',
];
$label    = $labels[$cat] ?? strtoupper($cat);
$labelW   = strlen($label) * 8 + 22;

/* ── Product shapes — pure SVG, NO PHP inside strings ─────── */
$shapes = [];

/* SMARTPHONE */
$shapes['smartphones'] = '
  <rect x="145" y="35" width="110" height="200" rx="16" fill="rgba(255,255,255,.08)" stroke="rgba(255,255,255,.18)" stroke-width="1.5"/>
  <rect x="152" y="55" width="96"  height="155" rx="6"  fill="rgba(120,180,255,.12)" stroke="rgba(120,180,255,.25)" stroke-width="1"/>
  <rect x="152" y="55" width="96"  height="60"  rx="6"  fill="rgba(100,160,255,.09)"/>
  <rect x="165" y="42" width="35"  height="10"  rx="5"  fill="rgba(0,0,0,.5)"/>
  <circle cx="178" cy="47" r="3.5" fill="rgba(60,60,80,.9)"  stroke="rgba(180,200,255,.3)" stroke-width=".8"/>
  <circle cx="188" cy="47" r="3.5" fill="rgba(60,60,80,.9)"  stroke="rgba(180,200,255,.3)" stroke-width=".8"/>
  <circle cx="196" cy="47" r="2"   fill="rgba(255,200,0,.5)"/>
  <line x1="162" y1="130" x2="238" y2="130" stroke="rgba(255,255,255,.15)" stroke-width="8"  stroke-linecap="round"/>
  <line x1="162" y1="148" x2="218" y2="148" stroke="rgba(255,255,255,.10)" stroke-width="6"  stroke-linecap="round"/>
  <line x1="162" y1="163" x2="228" y2="163" stroke="rgba(255,255,255,.07)" stroke-width="5"  stroke-linecap="round"/>
  <rect x="183" y="222" width="34" height="4" rx="2" fill="rgba(255,255,255,.30)"/>
  <rect x="255" y="100" width="3"  height="28" rx="1.5" fill="rgba(255,255,255,.25)"/>
  <rect x="142" y="95"  width="3"  height="20" rx="1.5" fill="rgba(255,255,255,.20)"/>
  <rect x="142" y="120" width="3"  height="20" rx="1.5" fill="rgba(255,255,255,.20)"/>
';

/* LAPTOP */
$shapes['laptops'] = '
  <rect x="75" y="30" width="250" height="165" rx="8" fill="rgba(255,255,255,.07)" stroke="rgba(255,255,255,.15)" stroke-width="1.5"/>
  <rect x="88" y="42" width="224" height="140" rx="4" fill="rgba(20,30,50,.6)"     stroke="rgba(100,150,255,.1)"  stroke-width=".5"/>
  <rect x="90" y="44" width="220" height="80"  rx="3" fill="rgba(100,150,255,.08)"/>
  <line x1="98"  y1="70"  x2="300" y2="70"  stroke="rgba(255,255,255,.12)" stroke-width="7" stroke-linecap="round"/>
  <line x1="98"  y1="86"  x2="260" y2="86"  stroke="rgba(255,255,255,.08)" stroke-width="5" stroke-linecap="round"/>
  <line x1="98"  y1="100" x2="280" y2="100" stroke="rgba(255,255,255,.06)" stroke-width="5" stroke-linecap="round"/>
  <circle cx="200" cy="47" r="3" fill="rgba(60,60,80,.8)" stroke="rgba(180,200,255,.2)" stroke-width=".5"/>
  <rect x="55"  y="193" width="290" height="28" rx="4" fill="rgba(255,255,255,.06)" stroke="rgba(255,255,255,.12)" stroke-width="1"/>
  <line x1="72"  y1="201" x2="328" y2="201" stroke="rgba(255,255,255,.10)" stroke-width="1"/>
  <line x1="72"  y1="210" x2="328" y2="210" stroke="rgba(255,255,255,.07)" stroke-width="1"/>
  <rect x="160" y="204" width="80"  height="14" rx="3" fill="rgba(255,255,255,.05)" stroke="rgba(255,255,255,.10)" stroke-width=".5"/>
  <rect x="40"  y="220" width="320" height="8"  rx="4" fill="rgba(255,255,255,.05)" stroke="rgba(255,255,255,.08)" stroke-width="1"/>
';
$shapes['gaming-laptops']   = $shapes['laptops'];
$shapes['business-laptops'] = $shapes['laptops'];

/* TABLET */
$shapes['tablets'] = '
  <rect x="95"  y="20" width="210" height="240" rx="14" fill="rgba(255,255,255,.07)" stroke="rgba(255,255,255,.16)" stroke-width="1.5"/>
  <rect x="108" y="32" width="184" height="210" rx="6"  fill="rgba(80,120,200,.10)"  stroke="rgba(120,170,255,.18)" stroke-width=".8"/>
  <rect x="112" y="36" width="176" height="80"  rx="4"  fill="rgba(100,150,255,.08)"/>
  <line x1="118" y1="60"  x2="280" y2="60"  stroke="rgba(255,255,255,.12)" stroke-width="7" stroke-linecap="round"/>
  <line x1="118" y1="76"  x2="255" y2="76"  stroke="rgba(255,255,255,.08)" stroke-width="5" stroke-linecap="round"/>
  <rect x="118" y="130" width="36" height="36" rx="8" fill="rgba(255,255,255,.07)"/>
  <rect x="162" y="130" width="36" height="36" rx="8" fill="rgba(255,255,255,.07)"/>
  <rect x="206" y="130" width="36" height="36" rx="8" fill="rgba(255,255,255,.07)"/>
  <rect x="118" y="174" width="36" height="36" rx="8" fill="rgba(255,255,255,.07)"/>
  <rect x="162" y="174" width="36" height="36" rx="8" fill="rgba(255,255,255,.07)"/>
  <rect x="206" y="174" width="36" height="36" rx="8" fill="rgba(255,255,255,.07)"/>
  <circle cx="200" cy="27" r="4" fill="rgba(40,40,60,.8)" stroke="rgba(180,200,255,.25)" stroke-width=".8"/>
  <rect x="305" y="80"  width="3" height="22" rx="1.5" fill="rgba(255,255,255,.22)"/>
  <rect x="92"  y="90"  width="3" height="35" rx="1.5" fill="rgba(255,255,255,.20)"/>
  <rect x="178" y="233" width="44" height="4"  rx="2"  fill="rgba(255,255,255,.28)"/>
';

/* SMARTWATCH */
$shapes['smartwatches'] = '
  <rect x="168" y="20"  width="64"  height="50"  rx="10" fill="rgba(255,255,255,.06)" stroke="rgba(255,255,255,.12)" stroke-width="1"/>
  <rect x="138" y="62"  width="124" height="136" rx="28" fill="rgba(255,255,255,.09)" stroke="rgba(255,255,255,.20)" stroke-width="1.5"/>
  <rect x="148" y="72"  width="104" height="116" rx="22" fill="rgba(20,20,40,.70)"    stroke="rgba(120,170,255,.20)" stroke-width=".8"/>
  <text x="200" y="118" text-anchor="middle" fill="rgba(255,255,255,.85)" font-family="Arial,sans-serif" font-size="26" font-weight="bold" letter-spacing="2">10:09</text>
  <text x="200" y="137" text-anchor="middle" fill="rgba(255,255,255,.40)" font-family="Arial,sans-serif" font-size="11">MON 19 JUN</text>
  <circle cx="200" cy="165" r="18" fill="none" stroke="rgba(255,80,80,.3)"  stroke-width="3"/>
  <circle cx="200" cy="165" r="18" fill="none" stroke="rgba(255,80,80,.7)"  stroke-width="3" stroke-dasharray="85 30" stroke-linecap="round"/>
  <circle cx="200" cy="165" r="13" fill="none" stroke="rgba(80,200,120,.6)" stroke-width="2.5" stroke-dasharray="60 21" stroke-linecap="round"/>
  <rect x="262" y="110" width="6" height="22" rx="3" fill="rgba(255,255,255,.20)" stroke="rgba(255,255,255,.10)" stroke-width=".5"/>
  <rect x="168" y="190" width="64" height="58" rx="10" fill="rgba(255,255,255,.06)" stroke="rgba(255,255,255,.12)" stroke-width="1"/>
';

/* GAMING CONSOLE */
$shapes['gaming'] = '
  <rect x="70"  y="60" width="260" height="160" rx="20" fill="rgba(255,255,255,.07)" stroke="rgba(255,255,255,.15)" stroke-width="1.5"/>
  <rect x="85"  y="105" width="8" height="70" rx="4" fill="rgba(0,0,0,.4)" stroke="rgba(255,255,255,.08)" stroke-width=".5"/>
  <rect x="95"  y="120" width="14" height="8"  rx="2" fill="rgba(0,0,0,.5)"/>
  <rect x="95"  y="134" width="14" height="8"  rx="2" fill="rgba(0,0,0,.5)"/>
  <circle cx="200" cy="140" r="28" fill="rgba(255,255,255,.05)" stroke="rgba(255,255,255,.15)" stroke-width="1"/>
  <path d="M190,128 L190,155 L196,152 L196,143 L206,148 Q212,151 212,145 Q212,138 206,136 Z" fill="rgba(255,255,255,.4)"/>
  <rect x="178" y="72"  width="44" height="12" rx="6" fill="rgba(0,0,0,.3)"/>
  <circle cx="196" cy="78" r="3"  fill="rgba(100,200,255,.6)"/>
  <circle cx="205" cy="78" r="3"  fill="rgba(255,255,255,.2)"/>
  <line x1="295" y1="90"  x2="315" y2="90"  stroke="rgba(255,255,255,.08)" stroke-width="1.5"/>
  <line x1="295" y1="97"  x2="315" y2="97"  stroke="rgba(255,255,255,.08)" stroke-width="1.5"/>
  <line x1="295" y1="104" x2="315" y2="104" stroke="rgba(255,255,255,.08)" stroke-width="1.5"/>
  <line x1="295" y1="111" x2="315" y2="111" stroke="rgba(255,255,255,.08)" stroke-width="1.5"/>
  <ellipse cx="155" cy="215" rx="40" ry="18" fill="rgba(255,255,255,.05)" stroke="rgba(255,255,255,.10)" stroke-width="1"/>
  <ellipse cx="245" cy="215" rx="40" ry="18" fill="rgba(255,255,255,.05)" stroke="rgba(255,255,255,.10)" stroke-width="1"/>
';

/* AUDIO / HEADPHONES */
$shapes['audio'] = '
  <ellipse cx="138" cy="155" rx="42" ry="52" fill="rgba(255,255,255,.07)" stroke="rgba(255,255,255,.15)" stroke-width="1.5"/>
  <ellipse cx="138" cy="155" rx="30" ry="38" fill="rgba(0,0,0,.25)"       stroke="rgba(255,255,255,.08)" stroke-width="1"/>
  <ellipse cx="262" cy="155" rx="42" ry="52" fill="rgba(255,255,255,.07)" stroke="rgba(255,255,255,.15)" stroke-width="1.5"/>
  <ellipse cx="262" cy="155" rx="30" ry="38" fill="rgba(0,0,0,.25)"       stroke="rgba(255,255,255,.08)" stroke-width="1"/>
  <path d="M138,108 Q200,30 262,108" fill="none" stroke="rgba(255,255,255,.18)" stroke-width="14" stroke-linecap="round"/>
  <path d="M138,108 Q200,30 262,108" fill="none" stroke="rgba(0,0,0,.20)"       stroke-width="8"  stroke-linecap="round"/>
  <path d="M160,82  Q200,46 240,82"  fill="none" stroke="rgba(255,255,255,.10)" stroke-width="10" stroke-linecap="round"/>
  <circle cx="138" cy="155" r="16"  fill="rgba(255,255,255,.04)" stroke="rgba(255,255,255,.10)" stroke-width=".5"/>
  <circle cx="262" cy="155" r="16"  fill="rgba(255,255,255,.04)" stroke="rgba(255,255,255,.10)" stroke-width=".5"/>
  <ellipse cx="138" cy="155" rx="42" ry="52" fill="none" stroke="rgba(255,150,50,.12)" stroke-width="3"/>
  <ellipse cx="262" cy="155" rx="42" ry="52" fill="none" stroke="rgba(255,150,50,.12)" stroke-width="3"/>
  <rect x="125" y="105" width="6" height="18" rx="3" fill="rgba(255,255,255,.15)"/>
  <rect x="269" y="105" width="6" height="18" rx="3" fill="rgba(255,255,255,.15)"/>
';

/* CAMERA */
$shapes['cameras'] = '
  <rect x="80" y="80" width="220" height="150" rx="12" fill="rgba(255,255,255,.07)" stroke="rgba(255,255,255,.16)" stroke-width="1.5"/>
  <rect x="80" y="90" width="35"  height="130" rx="10" fill="rgba(255,255,255,.05)" stroke="rgba(255,255,255,.10)" stroke-width=".8"/>
  <rect x="130" y="60" width="90" height="28"  rx="6"  fill="rgba(255,255,255,.07)" stroke="rgba(255,255,255,.12)" stroke-width="1"/>
  <circle cx="215" cy="157" r="56" fill="rgba(0,0,0,.35)"     stroke="rgba(255,255,255,.15)" stroke-width="2"/>
  <circle cx="215" cy="157" r="46" fill="rgba(0,0,0,.30)"     stroke="rgba(255,255,255,.10)" stroke-width="1.5"/>
  <circle cx="215" cy="157" r="34" fill="rgba(10,15,30,.50)"  stroke="rgba(100,150,255,.20)" stroke-width="1"/>
  <circle cx="215" cy="157" r="22" fill="rgba(5,10,20,.80)"   stroke="rgba(80,130,220,.30)"  stroke-width="1"/>
  <ellipse cx="207" cy="148" rx="8" ry="5" fill="rgba(255,255,255,.07)" transform="rotate(-30 207 148)"/>
  <circle cx="108" cy="75"  r="18" fill="rgba(255,255,255,.06)" stroke="rgba(255,255,255,.15)" stroke-width="1"/>
  <line   x1="108" y1="60" x2="108" y2="66" stroke="rgba(255,255,255,.4)" stroke-width="2"/>
  <circle cx="145" cy="73"  r="11" fill="rgba(255,255,255,.10)" stroke="rgba(255,255,255,.20)" stroke-width="1"/>
  <circle cx="145" cy="73"  r="7"  fill="rgba(255,255,255,.06)"/>
  <rect x="120" y="95" width="52" height="38" rx="3" fill="rgba(20,30,50,.6)" stroke="rgba(100,150,255,.15)" stroke-width=".5"/>
';

/* NETWORKING / ROUTER */
$shapes['networking'] = '
  <rect x="80" y="140" width="240" height="80" rx="10" fill="rgba(255,255,255,.07)" stroke="rgba(255,255,255,.15)" stroke-width="1.5"/>
  <line x1="120" y1="140" x2="100" y2="40"  stroke="rgba(255,255,255,.18)" stroke-width="6" stroke-linecap="round"/>
  <line x1="200" y1="140" x2="200" y2="35"  stroke="rgba(255,255,255,.18)" stroke-width="6" stroke-linecap="round"/>
  <line x1="280" y1="140" x2="300" y2="40"  stroke="rgba(255,255,255,.18)" stroke-width="6" stroke-linecap="round"/>
  <circle cx="100" cy="40"  r="5" fill="rgba(100,200,255,.6)"/>
  <circle cx="200" cy="35"  r="5" fill="rgba(100,200,255,.6)"/>
  <circle cx="300" cy="40"  r="5" fill="rgba(100,200,255,.6)"/>
  <circle cx="108" cy="162" r="4" fill="rgba(0,220,100,.8)"/>
  <circle cx="122" cy="162" r="4" fill="rgba(0,220,100,.6)"/>
  <circle cx="136" cy="162" r="4" fill="rgba(0,220,100,.6)"/>
  <circle cx="150" cy="162" r="4" fill="rgba(255,200,0,.7)"/>
  <path d="M175,105 Q200,78  225,105" fill="none" stroke="rgba(100,200,255,.5)"  stroke-width="3"   stroke-linecap="round"/>
  <path d="M160,118 Q200,82  240,118" fill="none" stroke="rgba(100,200,255,.3)"  stroke-width="2.5" stroke-linecap="round"/>
  <path d="M145,131 Q200,86  255,131" fill="none" stroke="rgba(100,200,255,.15)" stroke-width="2"   stroke-linecap="round"/>
  <circle cx="200" cy="120" r="4" fill="rgba(100,200,255,.8)"/>
  <rect x="220" y="175" width="12" height="8" rx="1" fill="rgba(0,0,0,.5)"         stroke="rgba(255,255,255,.12)" stroke-width=".5"/>
  <rect x="238" y="175" width="12" height="8" rx="1" fill="rgba(0,0,0,.5)"         stroke="rgba(255,255,255,.12)" stroke-width=".5"/>
  <rect x="256" y="175" width="12" height="8" rx="1" fill="rgba(0,0,0,.5)"         stroke="rgba(255,255,255,.12)" stroke-width=".5"/>
  <rect x="274" y="175" width="12" height="8" rx="1" fill="rgba(0,200,100,.4)"    stroke="rgba(255,255,255,.12)" stroke-width=".5"/>
';

/* DESKTOP COMPUTER */
$shapes['desktops'] = '
  <rect x="80"  y="30" width="240" height="160" rx="8" fill="rgba(255,255,255,.07)" stroke="rgba(255,255,255,.15)" stroke-width="1.5"/>
  <rect x="90"  y="40" width="220" height="138" rx="4" fill="rgba(20,30,50,.55)"    stroke="rgba(100,150,255,.12)" stroke-width=".5"/>
  <rect x="93"  y="43" width="214" height="70"  rx="3" fill="rgba(100,150,255,.06)"/>
  <line x1="100" y1="66"  x2="295" y2="66"  stroke="rgba(255,255,255,.12)" stroke-width="6" stroke-linecap="round"/>
  <line x1="100" y1="80"  x2="260" y2="80"  stroke="rgba(255,255,255,.08)" stroke-width="4" stroke-linecap="round"/>
  <line x1="100" y1="92"  x2="280" y2="92"  stroke="rgba(255,255,255,.05)" stroke-width="4" stroke-linecap="round"/>
  <circle cx="200" cy="170" r="3"  fill="rgba(100,200,255,.6)"/>
  <rect x="188" y="188" width="24" height="28" rx="2" fill="rgba(255,255,255,.07)" stroke="rgba(255,255,255,.10)" stroke-width="1"/>
  <rect x="165" y="213" width="70" height="8"  rx="3" fill="rgba(255,255,255,.07)" stroke="rgba(255,255,255,.10)" stroke-width="1"/>
  <rect x="292" y="130" width="55" height="98" rx="5" fill="rgba(255,255,255,.06)" stroke="rgba(255,255,255,.12)" stroke-width="1"/>
  <circle cx="312" cy="143" r="5" fill="rgba(100,200,255,.5)"/>
  <rect x="305" y="153" width="16" height="3" rx="1.5" fill="rgba(255,255,255,.15)"/>
  <line x1="297" y1="165" x2="342" y2="165" stroke="rgba(255,255,255,.07)" stroke-width="1.5"/>
  <line x1="297" y1="172" x2="342" y2="172" stroke="rgba(255,255,255,.07)" stroke-width="1.5"/>
  <line x1="297" y1="179" x2="342" y2="179" stroke="rgba(255,255,255,.07)" stroke-width="1.5"/>
';

/* PRINTER */
$shapes['printers'] = '
  <rect x="70"  y="80" width="260" height="130" rx="10" fill="rgba(255,255,255,.07)" stroke="rgba(255,255,255,.14)" stroke-width="1.5"/>
  <rect x="90"  y="195" width="200" height="8" rx="2" fill="rgba(255,255,255,.06)" stroke="rgba(255,255,255,.10)" stroke-width=".8"/>
  <rect x="100" y="200" width="180" height="5" rx="1" fill="rgba(255,255,255,.04)"/>
  <rect x="90"  y="78"  width="180" height="10" rx="3" fill="rgba(255,255,255,.05)" stroke="rgba(255,255,255,.08)" stroke-width=".5"/>
  <rect x="220" y="95"  width="90"  height="55" rx="6" fill="rgba(0,0,0,.2)"/>
  <circle cx="238" cy="110" r="6" fill="rgba(0,200,100,.7)"/>
  <circle cx="256" cy="110" r="5" fill="rgba(255,200,0,.5)"/>
  <rect x="234" y="122" width="52" height="20" rx="3" fill="rgba(100,150,255,.15)" stroke="rgba(100,150,255,.2)" stroke-width=".5"/>
  <rect x="88"  y="138" width="134" height="3" rx="1.5" fill="rgba(0,0,0,.4)"/>
  <circle cx="240" cy="192" r="3" fill="rgba(0,220,80,.8)"/>
  <rect x="90"  y="155" width="134" height="4" rx="2" fill="rgba(255,255,255,.08)"/>
  <line x1="90" y1="175" x2="200" y2="175" stroke="rgba(255,255,255,.05)" stroke-width="5"/>
';

/* ACCESSORIES — keyboard + mouse, all static SVG (no PHP loops) */
$shapes['accessories'] = '
  <rect x="60" y="150" width="280" height="90" rx="8" fill="rgba(255,255,255,.07)" stroke="rgba(255,255,255,.14)" stroke-width="1.5"/>
  <rect x="70"  y="163" width="22" height="9" rx="2" fill="rgba(255,255,255,.06)" stroke="rgba(255,255,255,.08)" stroke-width=".5"/>
  <rect x="96"  y="163" width="22" height="9" rx="2" fill="rgba(255,255,255,.06)" stroke="rgba(255,255,255,.08)" stroke-width=".5"/>
  <rect x="122" y="163" width="22" height="9" rx="2" fill="rgba(255,255,255,.06)" stroke="rgba(255,255,255,.08)" stroke-width=".5"/>
  <rect x="148" y="163" width="22" height="9" rx="2" fill="rgba(255,255,255,.06)" stroke="rgba(255,255,255,.08)" stroke-width=".5"/>
  <rect x="174" y="163" width="22" height="9" rx="2" fill="rgba(255,255,255,.06)" stroke="rgba(255,255,255,.08)" stroke-width=".5"/>
  <rect x="200" y="163" width="22" height="9" rx="2" fill="rgba(255,255,255,.06)" stroke="rgba(255,255,255,.08)" stroke-width=".5"/>
  <rect x="226" y="163" width="22" height="9" rx="2" fill="rgba(255,255,255,.06)" stroke="rgba(255,255,255,.08)" stroke-width=".5"/>
  <rect x="252" y="163" width="22" height="9" rx="2" fill="rgba(255,255,255,.06)" stroke="rgba(255,255,255,.08)" stroke-width=".5"/>
  <rect x="70"  y="177" width="22" height="9" rx="2" fill="rgba(255,255,255,.06)" stroke="rgba(255,255,255,.08)" stroke-width=".5"/>
  <rect x="96"  y="177" width="22" height="9" rx="2" fill="rgba(255,255,255,.06)" stroke="rgba(255,255,255,.08)" stroke-width=".5"/>
  <rect x="122" y="177" width="22" height="9" rx="2" fill="rgba(255,255,255,.06)" stroke="rgba(255,255,255,.08)" stroke-width=".5"/>
  <rect x="148" y="177" width="22" height="9" rx="2" fill="rgba(255,255,255,.06)" stroke="rgba(255,255,255,.08)" stroke-width=".5"/>
  <rect x="174" y="177" width="22" height="9" rx="2" fill="rgba(255,255,255,.06)" stroke="rgba(255,255,255,.08)" stroke-width=".5"/>
  <rect x="200" y="177" width="22" height="9" rx="2" fill="rgba(255,255,255,.06)" stroke="rgba(255,255,255,.08)" stroke-width=".5"/>
  <rect x="226" y="177" width="22" height="9" rx="2" fill="rgba(255,255,255,.06)" stroke="rgba(255,255,255,.08)" stroke-width=".5"/>
  <rect x="252" y="177" width="22" height="9" rx="2" fill="rgba(255,255,255,.06)" stroke="rgba(255,255,255,.08)" stroke-width=".5"/>
  <rect x="70"  y="191" width="22" height="9" rx="2" fill="rgba(255,255,255,.06)" stroke="rgba(255,255,255,.08)" stroke-width=".5"/>
  <rect x="96"  y="191" width="22" height="9" rx="2" fill="rgba(255,255,255,.06)" stroke="rgba(255,255,255,.08)" stroke-width=".5"/>
  <rect x="122" y="191" width="22" height="9" rx="2" fill="rgba(255,255,255,.06)" stroke="rgba(255,255,255,.08)" stroke-width=".5"/>
  <rect x="148" y="191" width="22" height="9" rx="2" fill="rgba(255,255,255,.06)" stroke="rgba(255,255,255,.08)" stroke-width=".5"/>
  <rect x="174" y="191" width="50" height="9" rx="2" fill="rgba(255,255,255,.06)" stroke="rgba(255,255,255,.08)" stroke-width=".5"/>
  <rect x="228" y="191" width="22" height="9" rx="2" fill="rgba(255,255,255,.06)" stroke="rgba(255,255,255,.08)" stroke-width=".5"/>
  <rect x="254" y="191" width="22" height="9" rx="2" fill="rgba(255,255,255,.06)" stroke="rgba(255,255,255,.08)" stroke-width=".5"/>
  <rect x="70"  y="205" width="50" height="9" rx="2" fill="rgba(255,255,255,.06)" stroke="rgba(255,255,255,.08)" stroke-width=".5"/>
  <rect x="124" y="205" width="120" height="9" rx="2" fill="rgba(255,255,255,.06)" stroke="rgba(255,255,255,.08)" stroke-width=".5"/>
  <rect x="248" y="205" width="22" height="9" rx="2" fill="rgba(255,255,255,.06)" stroke="rgba(255,255,255,.08)" stroke-width=".5"/>
  <rect x="232" y="50" width="68" height="98" rx="30" fill="rgba(255,255,255,.07)" stroke="rgba(255,255,255,.15)" stroke-width="1.5"/>
  <line x1="266" y1="50" x2="266" y2="105" stroke="rgba(255,255,255,.10)" stroke-width="1"/>
  <rect x="260" y="72" width="12" height="22" rx="6" fill="rgba(255,255,255,.12)" stroke="rgba(255,255,255,.15)" stroke-width=".5"/>
  <circle cx="266" cy="138" r="3" fill="rgba(0,200,255,.6)"/>
';

/* ── Select shape, fallback to generic electronics ─────────── */
$shape   = $shapes[$cat] ?? $shapes['accessories'];

/* ── Generate generic brand-logo style if no specific shape ── */
if (!isset($shapes[$cat])) {
    // Already falls back to accessories above
}

/* ── Output SVG ─────────────────────────────────────────────── */
echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 300" width="400" height="300">
  <defs>
    <linearGradient id="bg" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%"   stop-color="<?= $dark ?>"/>
      <stop offset="100%" stop-color="<?= $light ?>"/>
    </linearGradient>
    <radialGradient id="spot" cx="30%" cy="30%" r="65%">
      <stop offset="0%"   stop-color="rgba(255,255,255,0.09)"/>
      <stop offset="100%" stop-color="rgba(0,0,0,0)"/>
    </radialGradient>
    <pattern id="dots" x="0" y="0" width="24" height="24" patternUnits="userSpaceOnUse">
      <circle cx="1" cy="1" r=".8" fill="rgba(255,255,255,0.04)"/>
    </pattern>
  </defs>

  <!-- Background -->
  <rect width="400" height="300" fill="url(#bg)"/>
  <rect width="400" height="300" fill="url(#spot)"/>
  <rect width="400" height="300" fill="url(#dots)"/>

  <!-- Decorative circles -->
  <circle cx="370" cy="-20" r="130" fill="rgba(255,255,255,0.03)"/>
  <circle cx="-30" cy="330" r="160" fill="rgba(255,255,255,0.025)"/>

  <!-- Product illustration -->
  <?= $shape ?>

  <!-- Category badge -->
  <rect x="12" y="12" width="<?= $labelW ?>" height="22" rx="11"
        fill="rgba(0,0,0,0.35)" stroke="rgba(255,255,255,0.15)" stroke-width="0.5"/>
  <text x="22" y="27"
        fill="rgba(255,255,255,0.80)"
        font-family="Arial,Helvetica,sans-serif"
        font-size="10" font-weight="700" letter-spacing="1.5"><?= $label ?></text>

  <!-- Product name bar -->
<?php if ($name !== ''): ?>
  <rect x="0" y="262" width="400" height="38" fill="rgba(0,0,0,0.40)"/>
  <text x="200" y="284"
        text-anchor="middle"
        fill="rgba(255,255,255,0.88)"
        font-family="Arial,Helvetica,sans-serif"
        font-size="13" font-weight="600"><?= $name ?></text>
<?php endif; ?>

</svg>
