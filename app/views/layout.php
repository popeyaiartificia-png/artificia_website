<?php /** @var string $title, $description, $canonical, $robots, $ogType, $body, $ga4, $pixel, $gsc, $current; @var ?string $ogImage; @var array $jsonld, $settings; @var bool $hasForm */
$wa = whatsapp_url($settings);
// Cache-busting: each deploy changes the file time, so visitors never keep last week's CSS/JS (served with a 7-day max-age).
$asset = fn(string $p): string => $p . '?v=' . (@filemtime(dirname(__DIR__, 2) . '/public' . $p) ?: 0);
$nav = [
    '/' => 'Home',
    '/ai-masterclass' => 'Masterclass',
    '/corporate-ai-training' => 'Corporate training',
    '/services' => 'Solutions',
    '/about' => 'About',
    '/careers' => 'Careers',
    '/contact' => 'Contact',
];
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title) ?></title>
<?php if ($description !== ''): ?>
<meta name="description" content="<?= e($description) ?>">
<?php endif; ?>
<?php if ($robots !== ''): ?>
<meta name="robots" content="<?= e($robots) ?>">
<?php endif; ?>
<?php if ($canonical !== ''): ?>
<link rel="canonical" href="<?= e($canonical) ?>">
<meta property="og:url" content="<?= e($canonical) ?>">
<?php endif; ?>
<meta property="og:site_name" content="Artificia AI">
<meta property="og:type" content="<?= e($ogType) ?>">
<meta property="og:title" content="<?= e($title) ?>">
<meta property="og:description" content="<?= e($description) ?>">
<meta name="twitter:card" content="<?= $ogImage ? 'summary_large_image' : 'summary' ?>">
<meta name="twitter:title" content="<?= e($title) ?>">
<meta name="twitter:description" content="<?= e($description) ?>">
<?php if ($ogImage): ?>
<meta property="og:image" content="<?= e($ogImage) ?>">
<meta name="twitter:image" content="<?= e($ogImage) ?>">
<?php endif; ?>
<?php if ($gsc !== ''): ?>
<meta name="google-site-verification" content="<?= e($gsc) ?>">
<?php endif; ?>
<?php if ($jsonld): ?>
<script type="application/ld+json"><?= json_encode(['@context' => 'https://schema.org', '@graph' => $jsonld], JSON_HEX_TAG | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>
<?php endif; ?>
<?php // Enable the intro on every page load; motion still respects the visitor's preference. ?>
<script>(function(d){d.classList.add('js');if(!matchMedia('(prefers-reduced-motion: reduce)').matches){d.classList.add('motion');setTimeout(function(){if(!window.__motion)d.classList.remove('motion')},2500)}})(document.documentElement)</script>
<link rel="preload" href="/assets/img/artificia-logo.png" as="image">
<link rel="preload" href="/assets/fonts/artificia-sans-variable.woff2" as="font" type="font/woff2" crossorigin>
<link rel="preload" href="/assets/fonts/statement-sans.woff2" as="font" type="font/woff2" crossorigin>
<link rel="stylesheet" href="<?= $asset('/assets/css/site.css') ?>">
<?php if (str_contains($body, 'class="course-page"') || str_contains($body, 'journey-hero')): // the course page, and every page using its hero layout ?>
<link rel="stylesheet" href="<?= $asset('/assets/css/course.css') ?>">
<link rel="stylesheet" href="<?= $asset('/assets/css/journey.css') ?>">
<script src="<?= $asset('/assets/js/course.js') ?>" defer></script>
<?php endif ?>
<?php if (str_contains($body, 'class="solutions-page')): ?>
<link rel="stylesheet" href="<?= $asset('/assets/css/solutions.css') ?>">
<script src="<?= $asset('/assets/js/solutions.js') ?>" defer></script>
<?php endif ?>
<?php if (str_contains($body, 'class="solutions-page about-page')): // About, and Contact which shares its sheet ?>
<link rel="stylesheet" href="<?= $asset('/assets/css/about.css') ?>">
<?php endif ?>
<?php if (str_contains($body, 'class="solutions-page careers-page"')): ?>
<link rel="stylesheet" href="<?= $asset('/assets/css/careers.css') ?>">
<?php endif ?>
<?php if (str_contains($body, 'class="solutions-page corporate-page"')): ?>
<link rel="stylesheet" href="<?= $asset('/assets/css/corporate.css') ?>">
<script src="<?= $asset('/assets/js/corporate.js') ?>" defer></script>
<?php endif ?>
<link rel="icon" href="/assets/img/favicon.png" type="image/png">
<meta name="theme-color" content="#15160F">
<?php if ($ga4 !== ''): ?>
<script async src="https://www.googletagmanager.com/gtag/js?id=<?= $ga4 ?>"></script>
<script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments)}gtag('js',new Date());gtag('config','<?= $ga4 ?>');</script>
<?php endif; ?>
<?php if ($pixel !== ''): ?>
<script>!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,'script','https://connect.facebook.net/en_US/fbevents.js');fbq('init', '<?= $pixel ?>');fbq('track', 'PageView');</script>
<noscript><img height="1" width="1" style="display:none" alt="" src="https://www.facebook.com/tr?id=<?= $pixel ?>&amp;ev=PageView&amp;noscript=1"></noscript>
<?php endif; ?>
<script src="/assets/vendor/gsap.min.js" defer></script>
<script src="/assets/vendor/ScrollTrigger.min.js" defer></script>
<script src="<?= $asset('/assets/js/site.js') ?>" defer></script>
</head>
<body>
<div class="loader" aria-hidden="true">
  <span class="loader__mark">
    <span class="loader__piece"></span>
    <span class="loader__piece"></span>
    <span class="loader__piece"></span>
    <span class="loader__piece"></span>
    <span class="loader__piece"></span>
  </span>
</div>
<a class="skip" href="#main">Skip to content</a>
<header class="bind top" data-top>
  <div class="wrap top__in">
    <a class="brand" href="/"><img src="/assets/img/artificia-logo.png" width="492" height="138" alt="Artificia AI home"></a>
    <nav class="nav" id="site-nav" aria-label="Main">
      <ul>
<?php foreach ($nav as $href => $label): ?>
        <li><a href="<?= e($href) ?>"<?= $current === $href || str_starts_with($current, $href . '/') ? ' aria-current="page"' : '' ?>><?= e($label) ?></a></li>
<?php endforeach ?>
      </ul>
    </nav>
    <a class="btn btn--lime top__cta" href="<?= $hasForm ? '#enquire' : '/contact' ?>">Enquire</a>
    <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="site-nav"><?= icon('list') ?><span>Menu</span></button>
  </div>
</header>
<main id="main" class="sheet">
<?= $body ?>
</main>
<?php
// Shared footer navigation for the main public pages.
$explore = [
    ['/services', 'Solutions', [['/services/gohighlevel', 'GoHighLevel'], ['/services/website-development', 'Website development'], ['/services/content-marketing', 'Content and marketing'], ['/services/rag-custom-automation', 'RAG and custom automation']]],
    ['/corporate-ai-training', 'Corporate training', [['/corporate-ai-training/manufacturing', 'Manufacturing'], ['/corporate-ai-training/healthcare', 'Healthcare']]],
    ['/ai-masterclass', 'AI Masterclass', [['/ai-masterclass/students', 'For students'], ['/ai-masterclass/working-professionals', 'For working professionals'], ['/ai-masterclass/business-owners', 'For business owners']]],
    ['/about', 'About', []],
    ['/careers', 'Careers', []],
    ['/blog', 'Blog', []],
];
$phone = (string) ($settings['org_phone'] ?? '');
$email = (string) ($settings['org_email'] ?? '');
$talk = array_filter([
    [$wa, 'WhatsApp'],
    $phone !== '' ? ['tel:' . preg_replace('/[^\d+]/', '', $phone), 'Call us'] : null,
    $email !== '' ? ['mailto:' . $email, 'Email'] : null,
    ['/contact', 'Contact'],
]);
$offers = ['AI Automation', 'Agentic AI', 'Software & Apps', 'Marketing Automation', 'SEO / AEO / GEO', 'Cloud & Private AI', 'Corporate AI Training', 'AI Masterclass'];
$star = '<svg class="foot__star" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 0c1.6 7.7 4.3 10.4 12 12-7.7 1.6-10.4 4.3-12 12-1.6-7.7-4.3-10.4-12-12C7.7 10.4 10.4 7.7 12 0z"/></svg>';
$inSection = fn(string $p) => $current === $p || str_starts_with($current, $p . '/');
$footCta = match (true) {
    $inSection('/ai-masterclass') => 'Enquire About the Course',
    $inSection('/corporate-ai-training') => 'Enquire About Corporate Training',
    $inSection('/careers') => 'Apply for a Role',
    default => 'Book Free AI Audit',
};
$bigLink = fn(string $href, string $label) => '<a href="' . e($href) . '"' . ($href === $wa ? ' target="_blank" rel="noopener noreferrer"' : '') . '><span data-t="' . e($label) . '">' . e($label) . '</span></a>';
?>
<footer class="foot" data-foot>
  <div class="foot__rise" aria-hidden="true"><canvas class="topo-bg"></canvas></div>
  <div class="foot__panel">
    <canvas class="topo-bg" aria-hidden="true"></canvas>
    <p class="foot__say">
      <span class="foot__say-a">Let&rsquo;s put </span>
      <span class="foot__say-ai"><em>AI</em><svg class="foot__mark" viewBox="0 0 100 100" preserveAspectRatio="none" aria-hidden="true"><path pathLength="1" d="M31 9C61 0 95 11 98 37 104 65 80 87 49 91 20 97 1 78 4 51 6 26 31 6 59 7"/></svg></span>
      <span class="foot__say-b"> to <em><span class="foot__kern">w</span>ork.</em></span>
    </p>
    <div class="foot__run" aria-hidden="true"><div class="foot__run-in">
<?php for ($i = 0; $i < 4; $i++): ?>
      <span class="foot__run-g"><?php foreach ($offers as $n => $offer): ?><span<?= $n % 2 ? ' class="is-serif"' : '' ?>><?= e($offer) ?></span><?= $star ?><?php endforeach ?></span>
<?php endfor ?>
    </div></div>
    <div class="foot__figure" aria-hidden="true">
      <img class="foot__robot" src="/assets/img/footer/robot-neon-plain.webp" srcset="/assets/img/footer/robot-neon-plain-640.webp 640w, /assets/img/footer/robot-neon-plain.webp 1220w" sizes="(min-width: 1100px) 900px, 130vw" width="1220" height="1150" alt="" loading="lazy" decoding="async">
    </div>
    <div class="foot__grid">
      <nav class="foot__col" aria-label="Explore">
        <p class="foot__h">Explore</p>
        <ul class="foot__big">
<?php foreach ($explore as [$href, $label, $subs]): ?>
          <li><?= $bigLink($href, $label) ?>
<?php if ($subs): ?>
            <ul class="foot__sub">
<?php foreach ($subs as [$subHref, $subLabel]): ?>
              <li><a href="<?= e($subHref) ?>"><?= e($subLabel) ?></a></li>
<?php endforeach ?>
            </ul>
<?php endif ?>
          </li>
<?php endforeach ?>
        </ul>
      </nav>
      <nav class="foot__col foot__col--talk" aria-label="Talk to us">
        <p class="foot__h">Talk to us</p>
        <ul class="foot__big">
<?php foreach ($talk as [$href, $label]): ?>
          <li><?= $bigLink($href, $label) ?></li>
<?php endforeach ?>
        </ul>
        <div class="foot__where">
<?php if ($phone !== ''): ?>
          <p><?= e($phone) ?></p>
<?php endif ?>
<?php if ($email !== ''): ?>
          <p><?= e($email) ?></p>
<?php endif ?>
<?php if (!empty($settings['org_address'])): ?>
          <address><?= nl2br(e($settings['org_address'])) ?></address>
<?php endif ?>
        </div>
      </nav>
    </div>
  </div>
  <a class="foot__cta" href="<?= $hasForm ? '#enquire' : '/contact' ?>"><span class="foot__cta-in"><?= e($footCta) ?><?= icon('arrow-right') ?></span></a>
  <div class="foot__base">
    <p>&copy; <?= date('Y') ?> <b>Artificia Intellec Pvt Ltd</b></p>
    <p class="foot__legal"><a href="/privacy">Privacy</a><a href="/terms">Terms</a></p>
  </div>
</footer>
<a class="wa-float" href="<?= e($wa) ?>" target="_blank" rel="noopener noreferrer" aria-label="Chat with us on WhatsApp"><?= icon('whatsapp-logo') ?></a>
</body>
</html>
