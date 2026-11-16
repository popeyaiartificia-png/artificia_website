/* Artificia section blocks. One block per section type; its fields are edited in a form generated from
   app/sections/registry.php (window.artificiaSections), so a new field there appears here with no change.
   Sections render on the server exactly as on the live site: use Preview to see the page. No build step. */
(function (wp, registry) {
  'use strict';
  const { createElement: h } = wp.element;
  const { registerBlockType } = wp.blocks;
  const { useBlockProps, InnerBlocks, MediaUpload, MediaUploadCheck } = wp.blockEditor;
  const { TextControl, TextareaControl, SelectControl, ToggleControl, Button, BaseControl } = wp.components;
  const { useSelect, useDispatch } = wp.data;
  const plain = { __next40pxDefaultSize: true, __nextHasNoMarginBottom: true };

  const richField = (fields) => Object.keys(fields).find((k) => fields[k].type === 'richtext');
  const blockName = (type) => 'artificia/' + type.replace(/_/g, '-'); // block names allow dashes, not underscores
  const richParents = Object.keys(registry).filter((t) => richField(registry[t].fields)).map(blockName);

  // Paragraphs, headings (h2/h3) and lists exist only inside a section's rich-text field.
  wp.hooks.addFilter('blocks.registerBlockType', 'artificia/rich-text-only', function (settings, name) {
    if (name === 'core/paragraph') return { ...settings, parent: richParents };
    if (name === 'core/list') return { ...settings, parent: richParents.concat('core/list-item') };
    if (name === 'core/heading') {
      const levels = { ...(settings.attributes.levelOptions || { type: 'array' }), default: [2, 3] };
      return { ...settings, parent: richParents, attributes: { ...settings.attributes, levelOptions: levels } };
    }
    return settings;
  });

  function ImageField({ label, value, onChange }) {
    const media = useSelect((select) => (value ? select('core').getMedia(value) : null), [value]);
    const thumb = media && ((media.media_details && media.media_details.sizes && media.media_details.sizes.medium && media.media_details.sizes.medium.source_url) || media.source_url);
    return h(BaseControl, { label, __nextHasNoMarginBottom: true },
      h('div', { className: 'artificia-image' },
        thumb ? h('img', { src: thumb, alt: media.alt_text || '' }) : null,
        h(MediaUploadCheck, null, h(MediaUpload, {
          allowedTypes: ['image'], value,
          onSelect: (m) => onChange(m.id),
          render: ({ open }) => h(Button, { variant: 'secondary', onClick: open }, value ? 'Replace image' : 'Choose image'),
        })),
        value ? h(Button, { variant: 'link', isDestructive: true, onClick: () => onChange(undefined) }, 'Remove') : null));
  }

  function ListField({ def, label, value, onChange }) {
    const items = Array.isArray(value) ? value : [];
    const first = Object.keys(def.fields).find((k) => ['text', 'textarea'].includes(def.fields[k].type));
    const move = (i, d) => { const n = items.slice(); [n[i], n[i + d]] = [n[i + d], n[i]]; onChange(n); };
    return h('fieldset', { className: 'artificia-list' },
      h('legend', null, label),
      items.map((item, i) => h('details', { key: i, className: 'artificia-list__item' },
        h('summary', null, (i + 1) + '. ' + (String(item[first] || '').slice(0, 90) || '(empty)')),
        h(Fields, { defs: def.fields, values: item, onChange: (v) => onChange(items.map((x, j) => (j === i ? v : x))) }),
        h('div', { className: 'artificia-list__tools' },
          h(Button, { size: 'small', variant: 'secondary', disabled: i === 0, onClick: () => move(i, -1) }, 'Move up'),
          h(Button, { size: 'small', variant: 'secondary', disabled: i === items.length - 1, onClick: () => move(i, 1) }, 'Move down'),
          h(Button, { size: 'small', variant: 'link', isDestructive: true, onClick: () => onChange(items.filter((_, j) => j !== i)) }, 'Remove')))),
      h(Button, { variant: 'secondary', onClick: () => onChange(items.concat({})) }, 'Add item'));
  }

  function Field({ def, value, onChange }) {
    const label = def.label + (def.required ? ' *' : '');
    switch (def.type) {
      case 'text': return h(TextControl, { ...plain, label, value: value || '', onChange });
      case 'textarea': return h(TextareaControl, { __nextHasNoMarginBottom: true, label, value: value || '', rows: 3, onChange });
      case 'select': return h(SelectControl, {
        ...plain, label, value: value || '',
        options: [{ label: '(default)', value: '' }].concat(def.options.map((o) => ({ label: o, value: o }))),
        onChange: (v) => onChange(v === '' ? undefined : v),
      });
      case 'image': return h(ImageField, { label, value, onChange });
      case 'list': return h(ListField, { def, label, value, onChange });
      default: return null; // richtext: edited as the blocks inside the section
    }
  }

  function Fields({ defs, values, onChange }) {
    return h('div', { className: 'artificia-fields' }, Object.keys(defs).map((name) => h(Field, {
      key: name, def: defs[name], value: values[name],
      onChange: (v) => { const next = { ...values }; if (v === undefined) delete next[name]; else next[name] = v; onChange(next); },
    })));
  }

  Object.keys(registry).forEach(function (type) {
    const def = registry[type];
    const rich = richField(def.fields);
    registerBlockType(blockName(type), {
      apiVersion: 3,
      title: def.label,
      category: 'artificia',
      icon: 'layout',
      attributes: { fields: { type: 'object', default: {} } },
      supports: { html: false, className: false, customClassName: false },
      edit: function Edit({ attributes, setAttributes, isSelected }) {
        const fields = attributes.fields || {};
        const summary = fields.heading || fields.eyebrow || fields.text || fields.title || '';
        return h('div', useBlockProps({ className: 'artificia-section' + (isSelected ? ' is-open' : '') }),
          h('p', { className: 'artificia-section__label' }, def.label, summary ? h('span', null, String(summary).replace(/\*/g, '')) : null),
          isSelected ? h(Fields, { defs: def.fields, values: fields, onChange: (v) => setAttributes({ fields: v }) }) : null,
          rich ? h('div', { className: 'artificia-section__rich' },
            h('p', { className: 'artificia-section__rich-label' }, def.fields[rich].label),
            h(InnerBlocks, { allowedBlocks: ['core/paragraph', 'core/heading', 'core/list'], template: [['core/paragraph']] })) : null);
      },
      save: rich ? () => h(InnerBlocks.Content) : () => null,
    });
  });

  // The page's own fields from the PHP site: its heading (the hero shows it as the h1) and SEO.
  const DocumentPanel = (wp.editor && wp.editor.PluginDocumentSettingPanel) || (wp.editPost && wp.editPost.PluginDocumentSettingPanel);
  wp.plugins.registerPlugin('artificia-page', {
    render: function PagePanel() {
      const postType = useSelect((s) => s('core/editor').getCurrentPostType(), []);
      const meta = useSelect((s) => s('core/editor').getEditedPostAttribute('meta') || {}, []);
      const { editPost } = useDispatch('core/editor');
      if (!DocumentPanel || !['page', 'post'].includes(postType)) return null;
      const get = (k) => meta['_artificia_' + k];
      const set = (k) => (v) => editPost({ meta: { ['_artificia_' + k]: v } });
      return h(DocumentPanel, { name: 'artificia-page', title: 'Page heading and SEO' },
        h(TextControl, { ...plain, label: 'Page heading (h1)', value: get('h1') || '', onChange: set('h1') }),
        h(TextControl, { ...plain, label: 'Meta title', help: (get('meta_title') || '').length + ' characters', value: get('meta_title') || '', onChange: set('meta_title') }),
        h(TextareaControl, { __nextHasNoMarginBottom: true, label: 'Meta description', help: (get('meta_description') || '').length + ' characters', value: get('meta_description') || '', onChange: set('meta_description') }),
        h(TextControl, { ...plain, label: 'Focus keyword', value: get('focus_keyword') || '', onChange: set('focus_keyword') }),
        h(TextControl, { ...plain, label: 'Canonical address (leave empty for this page)', value: get('canonical') || '', onChange: set('canonical') }),
        h(ToggleControl, { __nextHasNoMarginBottom: true, label: 'Hide from search engines (noindex)', checked: !!get('noindex'), onChange: set('noindex') }),
        h(ToggleControl, { __nextHasNoMarginBottom: true, label: 'Automatic schema', checked: get('schema_auto') !== false, onChange: set('schema_auto') }),
        h(TextareaControl, { __nextHasNoMarginBottom: true, label: 'Extra JSON-LD schema (optional)', value: get('schema_custom') || '', rows: 5, onChange: set('schema_custom') }),
        h(ImageField, { label: 'Social share image', value: get('og_image') || undefined, onChange: (v) => set('og_image')(v || 0) }));
    },
  });
})(window.wp, window.artificiaSections);
