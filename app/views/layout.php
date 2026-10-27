<?php /** @var string $title, $description, $canonical, $robots, $ogType, $body, $ga4, $pixel, $gsc, $current; @var ?string $ogImage; @var array $jsonld, $settings; @var bool $hasForm */
$wa = whatsapp_url($settings);
$nav = [
    '/ai-masterclass' => 'Masterclass',
    '/corporate-ai-training' => 'Corporate training',
    '/services' => 'Services',
    '/case-studies/orkay-tiles' => 'Case study',
    '/about' => 'About',
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
<?php // 'motion' is promised only without a reduced-motion preference, and withdrawn if GSAP never arrives. ?>
<script>(function(d){d.classList.add('js');if(!matchMedia('(prefers-reduced-motion: reduce)').matches){d.classList.add('motion');setTimeout(function(){if(!window.__motion)d.classList.remove('motion')},2500)}})(document.documentElement)</script>
<link rel="preload" href="/assets/fonts/roboto-flex-var-latin.woff2" as="font" type="font/woff2" crossorigin>
<link rel="stylesheet" href="/assets/css/site.css">
<link rel="icon" href="/assets/img/favicon.png" type="image/png">
<meta name="theme-color" content="#08080d">
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
<script src="/assets/js/site.js" defer></script>
</head>
<body>
<a class="skip" href="#main">Skip to content</a>
<header class="bind top" data-top>
  <div class="wrap top__in">
    <a class="brand" href="/"><img src="/assets/img/artificia-logo.png" width="225" height="46" alt="Artificia AI home"></a>
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
<footer class="bind foot">
  <div class="wrap foot__panel">
    <div class="foot__grid">
      <div class="foot__brand">
        <a class="brand" href="/"><img src="/assets/img/artificia-logo.png" width="225" height="46" alt="Artificia AI" loading="lazy"></a>
        <p>AI training and AI systems, built in Vadodara.</p>
<?php if (!empty($settings['org_address'])): ?>
        <address><?= nl2br(e($settings['org_address'])) ?></address>
<?php endif ?>
      </div>
      <nav aria-label="Learn"><p class="foot__h">Learn</p><ul>
        <li><a href="/ai-masterclass">AI Masterclass</a></li>
        <li><a href="/ai-masterclass/students">For students</a></li>
        <li><a href="/ai-masterclass/working-professionals">For working professionals</a></li>
        <li><a href="/ai-masterclass/business-owners">For business owners</a></li>
      </ul></nav>
      <nav aria-label="Teams"><p class="foot__h">Teams</p><ul>
        <li><a href="/corporate-ai-training">Corporate AI training</a></li>
        <li><a href="/corporate-ai-training/manufacturing">Manufacturing</a></li>
        <li><a href="/corporate-ai-training/healthcare">Healthcare</a></li>
      </ul></nav>
      <nav aria-label="Services"><p class="foot__h">Services</p><ul>
        <li><a href="/services/gohighlevel">GoHighLevel</a></li>
        <li><a href="/services/website-development">Website development</a></li>
        <li><a href="/services/content-marketing">Content and marketing</a></li>
        <li><a href="/services/rag-custom-automation">RAG and custom automation</a></li>
      </ul></nav>
      <nav aria-label="Company"><p class="foot__h">Company</p><ul>
        <li><a href="/about">About</a></li>
        <li><a href="/case-studies/orkay-tiles">Case study</a></li>
        <li><a href="/blog">Blog</a></li>
        <li><a href="/contact">Contact</a></li>
      </ul></nav>
    </div>
    <div class="foot__base">
      <p>&copy; <?= date('Y') ?> Artificia Intellec Pvt Ltd</p>
      <p><a href="/privacy">Privacy</a> &nbsp; <a href="/terms">Terms</a></p>
    </div>
  </div>
</footer>
<a class="wa-float" href="<?= e($wa) ?>" aria-label="Chat with us on WhatsApp"><?= icon('whatsapp-logo') ?></a>
</body>
</html>
