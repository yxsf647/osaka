<?php
require __DIR__ . '/config.php';
$cars = require __DIR__ . '/data/cars.php';
$infoSlides = require __DIR__ . '/data/info.php';

/** JPEG photos for a car: assets/cars/name.jpeg (+ name-2.jpeg ... name-8.jpeg) */
function car_photos(string $base): array {
    $out = [];
    $dir = __DIR__ . '/assets/cars/';
    foreach ([''] + range(2, 8) as $n) {
        $suffix = $n === '' ? '' : '-' . $n;
        foreach (['jpeg', 'jpg', 'JPEG', 'JPG'] as $ext) {
            $f = $base . $suffix . '.' . $ext;
            if (is_file($dir . $f)) {
                $out[] = 'assets/cars/' . rawurlencode($f) . '?v=' . filemtime($dir . $f);
                break;
            }
        }
    }
    return $out;
}
function hex_rgb(string $hex): string {
    $hex = ltrim($hex, '#');
    if (strlen($hex) === 3) $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
    return hexdec(substr($hex,0,2)).','.hexdec(substr($hex,2,2)).','.hexdec(substr($hex,4,2));
}
$h = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');

$logo = is_file(__DIR__ . '/' . $company['logo_local'])
    ? $company['logo_local'] . '?v=' . filemtime(__DIR__ . '/' . $company['logo_local'])
    : $company['logo_remote'];

$logoCircle = is_file(__DIR__ . '/' . $company['logo_circle'])
    ? $company['logo_circle'] . '?v=' . filemtime(__DIR__ . '/' . $company['logo_circle'])
    : $logo;

// Order: hand-picked (featured) cars first, then the rest of the inventory
$featured = $others = [];
foreach ($cars as $c) {
    $c['photos'] = car_photos($c['image']);
    $c['mileage_fmt'] = number_format($c['mileage']) . ' km';
    if (!empty($c['featured'])) $featured[] = $c; else $others[] = $c;
}
$groups = [
    ['label' => 'Hand-Picked For You', 'cars' => $featured],
    ['label' => 'From Our Showroom',   'cars' => $others],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= $h($company['name']) ?> - Reception Display</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=Montserrat:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
:root{
  --deep:<?= $h($colors['deep']) ?>;
  --mid:<?= $h($colors['mid']) ?>;
  --accent:<?= $h($colors['accent']) ?>;
  --accent-rgb:<?= hex_rgb($colors['accent']) ?>;
  --serif:"Cormorant Garamond",Georgia,"Times New Roman",serif;
  --sans:"Montserrat","Segoe UI",Roboto,Arial,sans-serif;
  --ease:cubic-bezier(.19,.8,.22,1);
}
*{box-sizing:border-box;margin:0;padding:0}
html,body{height:100%;overflow:hidden;background:var(--deep)}
body{font-family:var(--sans);color:#fff;cursor:none}
#dust{position:fixed;inset:0;z-index:1;pointer-events:none}

.bgfx{position:fixed;inset:0;z-index:0;background:radial-gradient(120% 100% at 50% 0%,var(--mid),var(--deep) 75%)}
.bgfx i{position:absolute;border-radius:50%;filter:blur(8vh);opacity:.25;background:rgba(var(--accent-rgb),.55);animation:float 18s ease-in-out infinite alternate}
.bgfx i:nth-child(1){width:45vh;height:45vh;left:-8vh;top:10vh}
.bgfx i:nth-child(2){width:55vh;height:55vh;right:-12vh;bottom:-10vh;animation-duration:24s;animation-delay:-6s}
@keyframes float{to{transform:translate(8vw,-6vh) scale(1.25)}}

/* ---------- top & bottom bars ---------- */
.top{position:fixed;z-index:20;top:0;left:0;right:0;height:11vh;display:flex;align-items:center;justify-content:space-between;padding:0 3vw;
  background:linear-gradient(to bottom,rgba(5,60,115,.7),transparent)}
.top .plate{height:9.4vh;display:flex;align-items:center;background:#fff;padding:.6vh .8vw;border-radius:1.4vh;box-shadow:0 0 3vh rgba(var(--accent-rgb),.35);transition:opacity .8s,transform .8s var(--ease)}
.top .plate img{height:100%;max-width:24vw;object-fit:contain}
body.intro .top .plate{opacity:0;transform:translateY(-3vh)}
.clock{text-align:right;line-height:1.15}
.clock b{display:block;font:500 4.4vh var(--serif);letter-spacing:.06em;font-variant-numeric:tabular-nums}
.clock span{font-size:1.7vh;letter-spacing:.25em;text-transform:uppercase;opacity:.7}

.bar{position:fixed;z-index:20;left:0;right:0;bottom:0;height:8vh;display:flex;align-items:center;justify-content:space-between;padding:0 3vw;
  background:rgba(8,88,160,.78);backdrop-filter:blur(1.2vh);border-top:1px solid rgba(var(--accent-rgb),.45);font-size:2vh;letter-spacing:.04em}
.bar .contacts{display:flex;gap:2.4vw;align-items:center}
.bar .contacts span{opacity:.9;white-space:nowrap}
.bar .contacts b{color:var(--accent);font-weight:500;margin-right:.6vw;text-transform:uppercase;font-size:1.6vh;letter-spacing:.18em}
.bar .tip{position:relative;height:3vh;width:34vw;text-align:right;color:var(--accent);font-style:italic;font-family:var(--serif);font-size:2.6vh}
.bar .tip em{position:absolute;right:0;top:0;opacity:0;transform:translateY(1.2vh);transition:all .9s var(--ease);font-style:italic;white-space:nowrap}
.bar .tip em.on{opacity:1;transform:none}
.prog{position:fixed;z-index:21;left:0;bottom:0;height:.5vh;width:0;background:linear-gradient(90deg,transparent,var(--accent));box-shadow:0 0 1.5vh var(--accent)}

/* ---------- slides ---------- */
.slide{position:fixed;inset:0;z-index:5;opacity:0;visibility:hidden;transition:opacity 1.3s ease,visibility 0s 1.3s}
.slide.active{opacity:1;visibility:visible;transition-delay:0s}
.rise{opacity:0}
.slide.active .rise{animation:rise 1.1s var(--ease) both;animation-delay:calc(var(--d,0)*1s)}
@keyframes rise{from{opacity:0;transform:translateY(5vh)}to{opacity:1;transform:none}}
.slide.active .fadein{animation:fade 1.4s ease both;animation-delay:calc(var(--d,0)*1s)}
@keyframes fade{from{opacity:0}to{opacity:1}}

/* ---------- greeting ---------- */
.greet{display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center}
.logo-stage{position:relative;width:34vh;height:34vh;display:flex;align-items:center;justify-content:center;margin-bottom:5vh}
.logo-stage .plate{position:relative;z-index:2;overflow:hidden;width:28vh;height:28vh;display:flex;align-items:center;justify-content:center;background:#fff;border-radius:50%;padding:0;
  box-shadow:0 0 0 .6vh rgba(var(--accent-rgb),.6),0 0 8vh rgba(var(--accent-rgb),.55);opacity:0}
.logo-stage .plate img{width:100%;height:100%;object-fit:cover}
.logo-stage .plate::after{content:"";position:absolute;inset:-20%;background:linear-gradient(115deg,transparent 40%,rgba(255,255,255,.9) 50%,transparent 60%);transform:translateX(-120%)}
.slide.active .logo-stage .plate{animation:plate 1.6s var(--ease) .3s both}
.slide.active .logo-stage .plate::after{animation:sweep 2.2s ease 1.6s infinite}
@keyframes plate{from{opacity:0;transform:scale(.4) rotateY(90deg)}to{opacity:1;transform:none}}
@keyframes sweep{0%{transform:translateX(-120%)}60%,100%{transform:translateX(120%)}}
.ring{position:absolute;inset:0;border-radius:50%;border:.25vh dashed rgba(var(--accent-rgb),.7);opacity:0}
.ring.r2{inset:-3vh;border-style:solid;border-color:rgba(var(--accent-rgb),.25)}
.slide.active .ring{animation:ringin 1.6s ease 1s both,spin 30s linear 1s infinite}
.slide.active .ring.r2{animation:ringin 1.6s ease 1.3s both,spin 45s linear 1.3s infinite reverse}
@keyframes ringin{to{opacity:1}}
@keyframes spin{to{transform:rotate(360deg)}}
.pulse{position:absolute;inset:0;border-radius:50%;border:.3vh solid rgba(var(--accent-rgb),.7);opacity:0}
.slide.active .pulse{animation:pulse 3.2s ease-out 1.8s infinite}
.slide.active .pulse.p2{animation-delay:3.4s}
@keyframes pulse{0%{transform:scale(.8);opacity:.8}100%{transform:scale(1.9);opacity:0}}
.hello{font:500 2.6vh var(--sans);letter-spacing:.7em;text-transform:uppercase;color:var(--accent);padding-left:.7em}
.hello-big{font:600 11vh/1.05 var(--serif);margin:1.5vh 0 1vh;min-height:12vh}
.letters span{display:inline-block;opacity:0;transform:translateY(60%) rotateX(80deg)}
.slide.active .letters span{animation:letter .9s var(--ease) both;animation-delay:calc(var(--d,2s) + var(--i)*.045s)}
@keyframes letter{to{opacity:1;transform:none}}
.company{font:300 3.4vh var(--sans);letter-spacing:.5em;text-transform:uppercase;margin-top:1vh;padding-left:.5em}
.gline{width:0;height:.25vh;margin:3vh auto;background:linear-gradient(90deg,transparent,var(--accent),transparent)}
.slide.active .gline{animation:grow 1.6s var(--ease) 2.4s both}
@keyframes grow{to{width:42vw}}
.tagline{font:italic 500 3.4vh var(--serif);letter-spacing:.12em;opacity:.9}

/* ---------- stats ---------- */
.stats{display:flex;flex-direction:column;align-items:center;justify-content:center;padding:11vh 4vw 9vh}
.eyebrow{font:500 2.2vh var(--sans);letter-spacing:.6em;text-transform:uppercase;color:var(--accent);padding-left:.6em}
.title{font:600 8.5vh var(--serif);margin:1.5vh 0 6vh;text-align:center}
.stat-row{display:flex;gap:8vw;align-items:center;justify-content:center}
.stat{position:relative;width:46vh;height:46vh;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center}
.stat svg{position:absolute;inset:0;width:100%;height:100%;transform:rotate(-90deg)}
.stat circle{fill:none;stroke-width:1.2}
.stat .track{stroke:rgba(255,255,255,.12)}
.stat .arc{stroke:var(--accent);stroke-linecap:round;stroke-dasharray:301.6;stroke-dashoffset:301.6;filter:drop-shadow(0 0 1.2vh rgba(var(--accent-rgb),.8))}
.slide.active .stat .arc{animation:arc 3s var(--ease) 1.2s forwards}
@keyframes arc{to{stroke-dashoffset:calc(301.6 * (1 - var(--pct)))}}
.stat .num{font:600 15vh/1 var(--serif);font-variant-numeric:tabular-nums}
.stat .num small{font-size:8vh;color:var(--accent)}
.stat .lbl{font:400 2.4vh var(--sans);letter-spacing:.3em;text-transform:uppercase;margin-top:1.6vh;opacity:.85;padding-left:.3em}
.chip{margin-top:5vh;padding:1.6vh 3vw;border:1px solid rgba(var(--accent-rgb),.6);border-radius:10vh;font:500 2.4vh var(--sans);letter-spacing:.2em;text-transform:uppercase;background:rgba(var(--accent-rgb),.1)}
.chip b{color:var(--accent);font-weight:600}

/* ---------- cars ---------- */
.car{--fw:min(80vh,90vw)}
.photos{position:absolute;inset:0;overflow:hidden;background:var(--deep)}
.photo{position:absolute;inset:0;opacity:0;transition:opacity 1.6s ease}
.photo::before,.photo::after{content:"";position:absolute}
.photo::before{inset:-6%;background:linear-gradient(to bottom,rgba(4,52,102,.3),rgba(4,52,102,.75)),var(--img) center/cover no-repeat;filter:blur(3.5vh) brightness(.8) saturate(1.1)}
.photo::after{left:50%;top:11vh;width:var(--fw);height:calc(var(--fw)*.75);transform:translateX(-50%);background:rgba(0,0,0,.25) var(--img) center/contain no-repeat;
  border:.3vh solid rgba(255,255,255,.8);border-radius:1.6vh;box-shadow:0 3vh 9vh rgba(2,30,70,.6),0 0 6vh rgba(var(--accent-rgb),.25)}
.photo.on{opacity:1}
.slide.active .photo.on::before{animation:kb 16s ease-out both}
.slide:nth-of-type(even).active .photo.on::before{animation-name:kb2}
.slide.active .photo.on::after{animation:framein 1.4s var(--ease) both}
@keyframes kb{from{transform:scale(1)}to{transform:scale(1.08)}}
@keyframes kb2{from{transform:scale(1.08)}to{transform:scale(1)}}
@keyframes framein{from{opacity:0;transform:translateX(-50%) scale(.93)}to{opacity:1;transform:translateX(-50%) scale(1)}}
.nophoto{position:absolute;left:50%;top:11vh;width:var(--fw);height:calc(var(--fw)*.75);transform:translateX(-50%);display:flex;flex-direction:column;align-items:center;justify-content:center;
  color:rgba(255,255,255,.7);font:500 2.6vh var(--sans);letter-spacing:.3em;text-transform:uppercase;text-align:center;border:.3vh dashed rgba(255,255,255,.5);border-radius:1.6vh;background:rgba(255,255,255,.06)}
.nophoto small{font-size:1.8vh;margin-top:1.2vh;letter-spacing:.1em;text-transform:none}
.vig{position:absolute;inset:0;background:linear-gradient(to bottom,rgba(4,52,102,.2),rgba(4,52,102,.55) 70%,rgba(4,52,102,.8))}
.light{position:absolute;inset:0;overflow:hidden;pointer-events:none}
.light::before{content:"";position:absolute;top:-20%;bottom:-20%;width:18vw;left:-30vw;background:linear-gradient(100deg,transparent,rgba(255,255,255,.1),transparent);transform:skewX(-18deg)}
.slide.active .light::before{animation:sweep2 3.6s ease .6s both}
@keyframes sweep2{to{left:130vw}}
.frame{position:absolute;left:50%;top:11vh;width:var(--fw);height:calc(var(--fw)*.75);transform:translateX(-50%);pointer-events:none}
.tag{position:absolute;top:2vh;left:2vh;padding:.9vh 1.4vw;border-radius:10vh;background:rgba(4,52,102,.7);backdrop-filter:blur(1vh);border:1px solid rgba(255,255,255,.4);
  font:500 1.8vh var(--sans);letter-spacing:.3em;text-transform:uppercase;color:#fff}
.idx{position:absolute;top:2vh;right:2vh;padding:.5vh 1.4vw;border-radius:10vh;background:rgba(4,52,102,.7);backdrop-filter:blur(1vh);border:1px solid rgba(255,255,255,.4);font:500 2.4vh var(--serif);letter-spacing:.2em}
.idx b{font-size:3.4vh;font-weight:600}
.cinfo{position:absolute;left:0;right:0;bottom:10.5vh;text-align:center}
.cname{font:600 7vh/1.05 var(--serif);text-shadow:0 .5vh 2.5vh rgba(0,0,0,.5)}
.cline{width:0;height:.3vh;background:#fff;box-shadow:0 0 1.5vh rgba(255,255,255,.8);margin:1.6vh auto 2vh}
.slide.active .cline{animation:growcar 1.4s var(--ease) .9s both}
@keyframes growcar{to{width:16vw}}
.specs{display:flex;gap:1.2vw;justify-content:center}
.specs div{padding:1.1vh 1.7vw;font:400 2.4vh var(--sans);letter-spacing:.12em;background:rgba(255,255,255,.14);backdrop-filter:blur(1vh);border:1px solid rgba(255,255,255,.35);border-radius:10vh}

/* ---------- financing / import ---------- */
.info{display:flex;flex-direction:column;justify-content:center;padding:11vh 4vw 9vh}
.info .title{text-align:left;margin:1.5vh 0 1.5vh}
.info .intro{font:italic 500 3.6vh var(--serif);opacity:.85;margin-bottom:7vh}
.steps{position:relative;display:flex;gap:1.4vw}
.steps::before{content:"";position:absolute;left:3vw;right:3vw;top:3.6vh;height:.2vh;background:linear-gradient(90deg,var(--accent),rgba(var(--accent-rgb),.1));transform-origin:left;transform:scaleX(0)}
.slide.active .steps::before{animation:lineX 2.2s var(--ease) .6s both}
@keyframes lineX{to{transform:scaleX(1)}}
.step{position:relative;flex:1;padding:0 .6vw}
.step em{display:flex;width:7.2vh;height:7.2vh;border-radius:50%;align-items:center;justify-content:center;font:600 3.6vh var(--serif);font-style:normal;color:var(--deep);
  background:var(--accent);box-shadow:0 0 3vh rgba(var(--accent-rgb),.6);margin-bottom:3vh}
.step .card{padding:2.8vh 1.4vw;min-height:23vh;border-radius:1.6vh;background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.16);backdrop-filter:blur(1vh)}
.step b{display:block;font:600 3.3vh var(--serif);margin-bottom:1.2vh;color:#fff}
.step span{font:300 2.3vh/1.45 var(--sans);opacity:.85}
</style>
</head>
<body class="intro">
<div class="bgfx"><i></i><i></i></div>
<canvas id="dust"></canvas>

<div class="top">
  <div class="plate"><img src="<?= $h($logo) ?>" alt="<?= $h($company['name']) ?>"></div>
  <div class="clock"><b id="time">--:--</b><span id="date"></span></div>
</div>

<!-- 1. Greeting + logo animation -->
<section class="slide greet" data-sec="<?= (int)$seconds['greeting'] ?>" data-intro="1">
  <div class="logo-stage">
    <span class="pulse"></span><span class="pulse p2"></span>
    <span class="ring"></span><span class="ring r2"></span>
    <div class="plate"><img id="introLogo" src="<?= $h($logoCircle) ?>" alt="<?= $h($company['name']) ?>"></div>
  </div>
  <div class="hello rise" style="--d:1.4">Karibu &bull; Welcome</div>
  <div class="hello-big letters" id="greeting" style="--d:1.9s"></div>
  <div class="company rise" style="--d:2.4"><?= $h($company['name']) ?></div>
  <div class="gline"></div>
  <div class="tagline rise" style="--d:3"><?= $h($company['tagline']) ?></div>
</section>

<!-- 2. Customer satisfaction + vehicles sold -->
<section class="slide stats" data-sec="<?= (int)$seconds['stats'] ?>">
  <div class="eyebrow rise" style="--d:.2">Our Promise</div>
  <div class="title rise" style="--d:.4">Trusted by Kenyans</div>
  <div class="stat-row">
    <div class="stat rise" style="--d:.7;--pct:<?= $stats['satisfaction'] / 100 ?>">
      <svg viewBox="0 0 100 100"><circle class="track" cx="50" cy="50" r="48"/><circle class="arc" cx="50" cy="50" r="48"/></svg>
      <div class="num"><span data-count="<?= (int)$stats['satisfaction'] ?>">0</span><small>%</small></div>
      <div class="lbl">Customer Satisfaction</div>
    </div>
    <div class="stat rise" style="--d:.9;--pct:1">
      <svg viewBox="0 0 100 100"><circle class="track" cx="50" cy="50" r="48"/><circle class="arc" cx="50" cy="50" r="48"/></svg>
      <div class="num"><span data-count="<?= (int)$stats['sold'] ?>">0</span><small>+</small></div>
      <div class="lbl">Vehicles Sold</div>
    </div>
  </div>
  <div class="chip rise" style="--d:1.6"><b>&#10003;</b> &nbsp;<?= $h($warranty) ?></div>
</section>

<!-- 3 & 4. Hand-picked cars, then the rest of the inventory -->
<?php foreach ($groups as $g): $total = count($g['cars']); foreach ($g['cars'] as $i => $c): ?>
<section class="slide car" data-sec="<?= (int)$seconds['car'] ?>">
  <div class="photos">
<?php if ($c['photos']): foreach ($c['photos'] as $k => $p): ?>
    <div class="photo<?= $k === 0 ? ' on' : '' ?>" style="--img:url('<?= $h($p) ?>')"></div>
<?php endforeach; else: ?>
    <div class="nophoto">Photo coming soon<small>Upload JPEG: assets/cars/<?= $h($c['image']) ?>.jpeg</small></div>
<?php endif; ?>
  </div>
  <div class="light"></div>
  <div class="frame fadein" style="--d:.5">
    <div class="tag"><?= $h($g['label']) ?></div>
    <div class="idx"><b><?= sprintf('%02d', $i + 1) ?></b> / <?= sprintf('%02d', $total) ?></div>
  </div>
  <div class="cinfo">
    <div class="cname rise" style="--d:.5"><?= $h($c['name']) ?></div>
    <div class="cline"></div>
    <div class="specs">
      <div class="rise" style="--d:1"><?= $h($c['year']) ?></div>
      <div class="rise" style="--d:1.15"><?= $h($c['mileage_fmt']) ?></div>
      <div class="rise" style="--d:1.3"><?= $h($c['transmission']) ?></div>
      <div class="rise" style="--d:1.45"><?= $h($c['fuel']) ?></div>
    </div>
  </div>
</section>
<?php endforeach; endforeach; ?>

<!-- 5. Financing + Import process -->
<?php foreach ($infoSlides as $sl): ?>
<section class="slide info" data-sec="<?= (int)$seconds['info'] ?>">
  <div class="eyebrow rise" style="--d:.2">Osaka Wings</div>
  <div class="title rise" style="--d:.4"><?= $h($sl['title']) ?></div>
  <div class="intro rise" style="--d:.6"><?= $h($sl['intro']) ?></div>
  <div class="steps">
<?php foreach ($sl['items'] as $n => $it): ?>
    <div class="step rise" style="--d:<?= 1 + $n * .35 ?>">
      <em><?= $n + 1 ?></em>
      <div class="card"><b><?= $h($it[0]) ?></b><span><?= $h($it[1]) ?></span></div>
    </div>
<?php endforeach; ?>
  </div>
</section>
<?php endforeach; ?>

<div class="bar">
  <div class="contacts">
    <span><b>Call</b><?= $h($company['phone']) ?></span>
    <span><b>Email</b><?= $h($company['email']) ?></span>
    <span><b>Visit</b>Ngong Road, Nairobi</span>
  </div>
  <div class="tip" id="tip">
<?php foreach ($highlights as $x): ?><em><?= $h($x) ?></em><?php endforeach; ?>
  </div>
</div>
<div class="prog" id="prog"></div>

<script>
const slides = [...document.querySelectorAll('.slide')];
let cur = -1, timer = null, photoTimer = null;

/* ---- greeting by time of day ---- */
function greeting(){
  const h = new Date().getHours();
  const t = h < 12 ? 'Good Morning' : h < 17 ? 'Good Afternoon' : 'Good Evening';
  const el = document.getElementById('greeting');
  el.innerHTML = [...t].map((ch,i) => `<span style="--i:${i}">${ch === ' ' ? '&nbsp;' : ch}</span>`).join('');
}

/* ---- count-up numbers ---- */
function countUp(root){
  root.querySelectorAll('[data-count]').forEach(el => {
    const end = +el.dataset.count, dur = 2600, t0 = performance.now() + 900;
    el.textContent = '0';
    (function step(now){
      const p = Math.min(Math.max((now - t0) / dur, 0), 1), e = 1 - Math.pow(1 - p, 4);
      el.textContent = Math.round(end * e);
      if (p < 1) requestAnimationFrame(step);
    })(performance.now());
  });
}

function show(i){
  slides.forEach(s => s.classList.remove('active'));
  const s = slides[i];
  // restart animations cleanly
  void s.offsetWidth;
  s.classList.add('active');
  document.body.classList.toggle('intro', !!s.dataset.intro);
  if (s.dataset.intro) greeting();
  countUp(s);

  // rotate extra photos of the same car
  clearInterval(photoTimer);
  const ph = [...s.querySelectorAll('.photo')];
  if (ph.length > 1){
    let k = 0, sec = +s.dataset.sec;
    photoTimer = setInterval(() => { ph[k].classList.remove('on'); k = (k + 1) % ph.length; ph[k].classList.add('on'); },
      Math.max(2500, sec * 1000 / ph.length));
  }

  const sec = +s.dataset.sec, bar = document.getElementById('prog');
  bar.style.transition = 'none'; bar.style.width = '0';
  requestAnimationFrame(() => requestAnimationFrame(() => { bar.style.transition = `width ${sec}s linear`; bar.style.width = '100%'; }));
  clearTimeout(timer);
  timer = setTimeout(next, sec * 1000);
}
function next(){
  cur++;
  if (cur >= slides.length) { location.reload(); return; }  // fresh start = greeting again + new photos
  show(cur);
}
next();
document.addEventListener('keydown', e => { if (e.key === 'ArrowRight') next(); });

/* ---- clock ---- */
function tick(){
  const d = new Date();
  document.getElementById('time').textContent = d.toLocaleTimeString('en-KE',{hour:'2-digit',minute:'2-digit'});
  document.getElementById('date').textContent = d.toLocaleDateString('en-KE',{weekday:'long',day:'numeric',month:'long'});
}
tick(); setInterval(tick, 10000);

/* ---- rotating highlight lines ---- */
const tips = [...document.querySelectorAll('#tip em')]; let ti = 0;
if (tips.length){ tips[0].classList.add('on'); setInterval(() => { tips[ti].classList.remove('on'); ti = (ti + 1) % tips.length; tips[ti].classList.add('on'); }, 5000); }

/* ---- floating gold dust ---- */
(function(){
  const c = document.getElementById('dust'), x = c.getContext('2d'); let W, H, P = [];
  function size(){ W = c.width = innerWidth; H = c.height = innerHeight; }
  addEventListener('resize', size); size();
  for (let i = 0; i < 70; i++) P.push({x:Math.random()*W, y:Math.random()*H, r:Math.random()*2+.4, v:Math.random()*.35+.08, a:Math.random()*.5+.15, s:Math.random()*Math.PI*2});
  function rgb(){ return getComputedStyle(document.documentElement).getPropertyValue('--accent-rgb').trim() || '201,164,92'; }
  (function loop(){
    x.clearRect(0,0,W,H); const col = rgb();
    P.forEach(p => { p.y -= p.v; p.s += .01; p.x += Math.sin(p.s)*.3; if (p.y < -5) { p.y = H + 5; p.x = Math.random()*W; }
      x.beginPath(); x.fillStyle = `rgba(${col},${p.a})`; x.arc(p.x, p.y, p.r, 0, 7); x.fill(); });
    requestAnimationFrame(loop);
  })();
})();

<?php if (!empty($match_logo)): ?>
/* ---- build the palette from the logo's main colour ---- */
(function(){
  const img = new Image(); img.crossOrigin = 'anonymous';
  img.onload = () => {
    try {
      const S = 80, cv = document.createElement('canvas'); cv.width = cv.height = S;
      const g = cv.getContext('2d'); g.drawImage(img, 0, 0, S, S);
      const d = g.getImageData(0, 0, S, S).data, bins = {};
      for (let i = 0; i < d.length; i += 4){
        const r = d[i], gg = d[i+1], b = d[i+2], a = d[i+3]; if (a < 140) continue;
        const mx = Math.max(r,gg,b), mn = Math.min(r,gg,b), l = (mx+mn)/510, sat = mx === mn ? 0 : (mx-mn)/(255-Math.abs(mx+mn-255));
        if (l > .9 || l < .12 || sat < .35) continue;             // ignore white, black, grey
        let hh; const df = mx - mn;
        if (mx === r) hh = ((gg-b)/df + 6) % 6; else if (mx === gg) hh = (b-r)/df + 2; else hh = (r-gg)/df + 4;
        const k = Math.round(hh*60/15); (bins[k] = bins[k] || {n:0,h:0,s:0}); bins[k].n += sat; bins[k].h += hh*60; bins[k].s += sat; bins[k].c = (bins[k].c||0)+1;
      }
      const best = Object.values(bins).sort((p,q) => q.n - p.n)[0]; if (!best) return;
      const H = Math.round(best.h / best.c), Sa = Math.min(100, Math.round(best.s / best.c * 100));
      const hsl = (h,s,l) => { s/=100; l/=100; const k = n => (n + h/30) % 12, a = s*Math.min(l,1-l), f = n => l - a*Math.max(-1,Math.min(k(n)-3,Math.min(9-k(n),1))); return [Math.round(255*f(0)),Math.round(255*f(8)),Math.round(255*f(4))]; };
      const R = document.documentElement.style, hx = a => '#' + a.map(v => v.toString(16).padStart(2,'0')).join('');
      const acc = hsl(H, Math.max(55,Sa), 58);
      R.setProperty('--accent', hx(acc)); R.setProperty('--accent-rgb', acc.join(','));
      R.setProperty('--deep', hx(hsl(H, Math.min(55,Sa), 6)));
      R.setProperty('--mid',  hx(hsl(H, Math.min(50,Sa), 14)));
    } catch(e) { /* logo not readable from this page: keep the default premium palette */ }
  };
  img.src = <?= json_encode($logo) ?>;
})();
<?php endif; ?>
</script>
</body>
</html>
