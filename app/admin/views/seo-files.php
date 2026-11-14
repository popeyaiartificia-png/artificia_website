<?php /** @var array $values, $errors; @var string $env, $robots, $llms */
$radio = fn(string $name, string $value, string $label) => '<label><input type="radio" name="' . $name . '" value="' . $value . '"' . ($values[$name] === $value ? ' checked' : '') . '> ' . e($label) . '</label>';
?>
<?= admin_page_head('SEO tools', 'robots.txt & llms.txt', null, 'What search engines and AI assistants are told about the site. Automatic is right for almost every case.') ?>
<?php if ($env !== 'production'): ?>
<p class="notice">This is the <?= e($env) ?> site: its robots.txt always blocks every crawler, whatever is saved here. The previews show what the live site serves.</p>
<?php endif ?>

<form method="post" action="/admin/seo/files" data-dirty-guard>
<?= csrf_field() ?>
<div class="cols">
  <section class="card">
    <h2>robots.txt</h2>
    <div class="check"><?= $radio('robots_mode', 'auto', 'Automatic') ?><?= $radio('robots_mode', 'custom', 'Custom text') ?></div>
    <p class="hint">Automatic allows every crawler, keeps /admin out, adds the Sitemap line and names the AI crawlers (<?= e(implode(', ', SEO_AI_BOTS)) ?>).</p>
    <div class="check">
      <label><input type="checkbox" name="robots_block_ai" value="1"<?= $values['robots_block_ai'] === '1' ? ' checked' : '' ?>> Block AI crawlers (Automatic only)</label>
      <p class="hint">Leave this off: ChatGPT, Claude, Perplexity and Google's AI answers can only cite pages they may read.</p>
    </div>
    <?= admin_field('robots_custom', 'Custom robots.txt', $values['robots_custom'], $errors['robots_custom'] ?? null, 'textarea', 'Served only when Custom is chosen. /admin stays blocked and the Sitemap line is added if you leave them out.', 'spellcheck="false" class="mono"') ?>
    <div class="check">
      <label><input type="checkbox" name="robots_confirm" value="1"> I know "Disallow: /" hides the whole site from that crawler</label>
      <p class="hint">Never tick this on the live site unless you mean it.</p>
    </div>
    <details><summary>Preview: robots.txt as the live site serves it</summary><pre class="mono"><?= e($robots) ?></pre></details>
  </section>

  <section class="card">
    <h2>llms.txt</h2>
    <div class="check"><?= $radio('llms_mode', 'auto', 'Automatic') ?><?= $radio('llms_mode', 'custom', 'Custom text') ?></div>
    <p class="hint">Tells AI assistants what the site is and which pages matter. Automatic lists every live, indexable page and post with its title and description.</p>
    <?= admin_field('llms_intro', 'Introduction (Automatic)', $values['llms_intro'], $errors['llms_intro'] ?? null, 'textarea', 'One or two sentences on who Artificia AI is and what it offers. Only facts you can stand behind.') ?>
    <?= admin_field('llms_extra', 'Extra sections (Automatic)', $values['llms_extra'], $errors['llms_extra'] ?? null, 'textarea', 'Added at the end, in Markdown: ## Heading, then - [Link](https://…): note lines.', 'spellcheck="false" class="mono"') ?>
    <?= admin_field('llms_custom', 'Custom llms.txt', $values['llms_custom'], $errors['llms_custom'] ?? null, 'textarea', 'Served only when Custom is chosen.', 'spellcheck="false" class="mono"') ?>
    <details><summary>Preview: llms.txt as it will be served</summary><pre class="mono"><?= e($llms) ?></pre></details>
  </section>
</div>
<div class="btn-row">
  <button class="btn btn--primary" type="submit" data-busy="Saving&hellip;">Save</button>
  <a class="btn btn--ghost" href="/robots.txt" target="_blank" rel="noopener" data-no-progress>robots.txt<?= icon('arrow-square-out') ?></a>
  <a class="btn btn--ghost" href="/llms.txt" target="_blank" rel="noopener" data-no-progress>llms.txt<?= icon('arrow-square-out') ?></a>
  <a class="btn btn--ghost" href="/sitemap.xml" target="_blank" rel="noopener" data-no-progress>sitemap.xml<?= icon('arrow-square-out') ?></a>
</div>
</form>
