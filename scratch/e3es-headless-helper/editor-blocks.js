(function(blocks, editor, element, components) {
    var el = element.createElement;
    var InnerBlocks = editor.InnerBlocks;
    var RichText = editor.RichText;
    var MediaUpload = editor.MediaUpload || blocks.MediaUpload || (window.wp.blockEditor && window.wp.blockEditor.MediaUpload);
    var InspectorControls = editor.InspectorControls;
    
    var PanelBody = components.PanelBody;
    var TextControl = components.TextControl;
    var TextareaControl = components.TextareaControl;
    var ToggleControl = components.ToggleControl;
    var SelectControl = components.SelectControl;
    var Button = components.Button;

    // Helper: Media Uploader Component
    function MediaSelect(props) {
        var useState = element.useState;
        var useEffect = element.useEffect;

        var [isOpen, setIsOpen] = useState(false);
        var [loading, setLoading] = useState(false);
        var [mediaData, setMediaData] = useState(null);
        var [error, setError] = useState(null);
        var [saving, setSaving] = useState(false);

        var [altText, setAltText] = useState('');
        var [titleText, setTitleText] = useState('');
        var [captionText, setCaptionText] = useState('');
        var [descText, setDescText] = useState('');

        useEffect(function() {
            if (!isOpen || !props.url) return;

            setLoading(true);
            setError(null);

            var urlStr = props.url;
            var filename = urlStr.substring(urlStr.lastIndexOf('/') + 1);
            var cleanBasename = filename.substring(0, filename.lastIndexOf('.'));
            var baseSearch = cleanBasename.replace(/-\d+x\d+$/, '').replace(/-\d+$/, '');

            wp.apiFetch({ path: '/wp/v2/media?search=' + encodeURIComponent(baseSearch) })
                .then(function(results) {
                    setLoading(false);
                    if (!results || results.length === 0) {
                        setError('No media found matching this image URL.');
                        return;
                    }

                    // Match by clean source_url
                    var cleanUrl = function(u) {
                        if (!u) return '';
                        var b = u.substring(u.lastIndexOf('/') + 1);
                        var extIdx = b.lastIndexOf('.');
                        if (extIdx !== -1) b = b.substring(0, extIdx);
                        return b.replace(/-\d+x\d+$/, '').replace(/-\d+$/, '').toLowerCase();
                    };

                    var targetClean = cleanUrl(urlStr);
                    var matched = null;
                    for (var i = 0; i < results.length; i++) {
                        if (cleanUrl(results[i].source_url) === targetClean) {
                            matched = results[i];
                            break;
                        }
                    }

                    // Fallback to first result if no exact match
                    if (!matched) {
                        matched = results[0];
                    }

                    setMediaData(matched);
                    setAltText(matched.alt_text || '');
                    setTitleText(matched.title ? (matched.title.raw || matched.title.rendered || '') : '');
                    setCaptionText(matched.caption ? (matched.caption.raw || matched.caption.rendered || '') : '');
                    setDescText(matched.description ? (matched.description.raw || matched.description.rendered || '') : '');
                })
                .catch(function(err) {
                    setLoading(false);
                    setError('Failed to fetch media details: ' + (err.message || err));
                });
        }, [isOpen, props.url]);

        var handleSave = function() {
            if (!mediaData) return;
            setSaving(true);

            wp.apiFetch({
                path: '/wp/v2/media/' + mediaData.id,
                method: 'POST',
                data: {
                    alt_text: altText,
                    title: titleText,
                    caption: captionText,
                    description: descText
                }
            })
            .then(function(updated) {
                setSaving(false);
                setMediaData(updated);
                setIsOpen(false);
                if (props.onSelect) {
                    props.onSelect(updated);
                }
            })
            .catch(function(err) {
                setSaving(false);
                setError('Failed to save changes: ' + (err.message || err));
            });
        };

        var modal = isOpen && el(components.Modal, {
            title: 'Image Details & Metadata',
            onRequestClose: function() { setIsOpen(false); },
            className: 'e3es-media-detail-modal',
            style: { maxWidth: '500px' }
        },
            loading && el('div', { style: { padding: '20px', textAlign: 'center' } },
                el(components.Spinner || 'span', {}, 'Loading details...')
            ),
            error && el('div', { style: { padding: '10px', color: '#cc1818', background: '#ffebeb', marginBottom: '15px', borderRadius: '4px' } }, error),
            !loading && mediaData && el('div', {},
                el('div', { style: { display: 'flex', gap: '15px', marginBottom: '20px' } },
                    el('div', { style: { width: '100px', height: '100px', border: '1px solid #ccc', overflow: 'hidden', background: '#f9f9f9', display: 'flex', alignItems: 'center', justifyContent: 'center' } },
                        el('img', { src: mediaData.source_url, style: { maxWidth: '100%', maxHeight: '100%', objectFit: 'contain' } })
                    ),
                    el('div', { style: { flex: 1, fontSize: '13px' } },
                        el('div', { style: { fontWeight: 'bold', marginBottom: '4px' } }, 'Filename:'),
                        el('div', { style: { wordBreak: 'break-all', color: '#666', marginBottom: '8px' } }, mediaData.media_details && mediaData.media_details.file ? mediaData.media_details.file.substring(mediaData.media_details.file.lastIndexOf('/') + 1) : ''),
                        el('a', { href: mediaData.source_url, target: '_blank', rel: 'noopener noreferrer', style: { color: '#007cba', textDecoration: 'underline' } }, 'View original file')
                    )
                ),
                el(components.TextControl, {
                    label: 'Title',
                    value: titleText,
                    onChange: setTitleText
                }),
                el(components.TextControl, {
                    label: 'Alternative Text (Alt Text)',
                    help: 'Describe the purpose of the image. Leave empty if decorative.',
                    value: altText,
                    onChange: setAltText
                }),
                el(components.TextareaControl, {
                    label: 'Caption',
                    value: captionText,
                    onChange: setCaptionText
                }),
                el(components.TextareaControl, {
                    label: 'Description',
                    value: descText,
                    onChange: setDescText
                }),
                el('div', { style: { display: 'flex', justifyContent: 'flex-end', gap: '10px', marginTop: '20px' } },
                    el(components.Button, { isSecondary: true, onClick: function() { setIsOpen(false); } }, 'Cancel'),
                    el(components.Button, { isPrimary: true, isBusy: saving, onClick: handleSave }, 'Save Changes')
                )
            )
        );

        return el(MediaUpload, {
            onSelect: props.onSelect,
            allowedTypes: ['image'],
            value: props.id,
            render: function(obj) {
                if (props.url) {
                    return el('div', { className: 'media-select-preview-wrapper', style: { width: '100%', marginBottom: '10px' } },
                        el('div', {
                            onClick: function() { setIsOpen(true); },
                            style: { marginBottom: '8px', border: '1px solid #ccc', padding: '5px', background: '#f5f5f5', display: 'flex', justifyContent: 'center', alignItems: 'center', height: '120px', overflow: 'hidden', cursor: 'pointer' },
                            title: 'Click to view image details'
                        },
                            el('img', { src: props.url, style: { maxWidth: '100%', maxHeight: '100%', objectFit: 'contain', display: 'block' } })
                        ),
                        el('div', { style: { display: 'flex', gap: '8px' } },
                            el(Button, { isSecondary: true, isSmall: true, onClick: obj.open, style: { flex: 1, justifyContent: 'center' } }, 'Replace Image'),
                            el(Button, { isSecondary: true, isSmall: true, onClick: function() { setIsOpen(true); }, style: { flex: 1, justifyContent: 'center' } }, 'Image Details')
                        ),
                        modal
                    );
                } else {
                    return el(Button, { isSecondary: true, isLarge: true, onClick: obj.open, style: { width: '100%', justifyContent: 'center' } }, 'Upload/Select Image');
                }
            }
        });
    }


    // 1. Verticals Accordion Hero (Parent)
    blocks.registerBlockType('e3es/verticals-hero', {
        title: 'E3 Accordion Hero',
        icon: 'slides',
        category: 'layout',
        edit: function(props) {
            return el('section', { className: 'verticals-hero' },
                el('div', { className: 'verticals-hero__container' },
                    el('div', { className: 'verticals-hero__accordion' },
                        el(InnerBlocks, { allowedBlocks: ['e3es/verticals-hero-option'] })
                    )
                )
            );
        },
        save: function(props) {
            return el('section', { className: 'verticals-hero' },
                el('div', { className: 'verticals-hero__container' },
                    el('div', { className: 'verticals-hero__accordion' },
                        el(InnerBlocks.Content)
                    )
                )
            );
        }
    });

    // 1b. Verticals Accordion Hero Option (Child)
    blocks.registerBlockType('e3es/verticals-hero-option', {
        title: 'E3 Accordion Option',
        icon: 'index-card',
        category: 'layout',
        parent: ['e3es/verticals-hero'],
        attributes: {
            title: { type: 'string', default: '' },
            text: { type: 'string', default: '' },
            btnText: { type: 'string', default: 'Learn More' },
            btnUrl: { type: 'string', default: '' },
            imageUrl: { type: 'string', default: '' },
            imageAlt: { type: 'string', default: '' },
            isActive: { type: 'boolean', default: true }
        },
        edit: function(props) {
            var attr = props.attributes;
            
            var inspector = el(InspectorControls, {},
                el(PanelBody, { title: 'Slide Settings', initialOpen: true },
                    el('div', { style: { marginBottom: '15px' } },
                        el('label', { style: { display: 'block', marginBottom: '5px', fontWeight: 'bold' } }, 'Slide Image'),
                        el(MediaSelect, {
                            url: attr.imageUrl,
                            onSelect: function(media) {
                                props.setAttributes({ imageUrl: media.url, imageAlt: media.alt || '' });
                            }
                        })
                    ),
                    el(TextControl, {
                        label: 'Heading Title',
                        value: attr.title,
                        onChange: function(val) { props.setAttributes({ title: val }); }
                    }),
                    el(TextareaControl, {
                        label: 'Description Text',
                        value: attr.text,
                        onChange: function(val) { props.setAttributes({ text: val }); }
                    }),
                    el(TextControl, {
                        label: 'Button Text',
                        value: attr.btnText,
                        onChange: function(val) { props.setAttributes({ btnText: val }); }
                    }),
                    el(TextControl, {
                        label: 'Button URL',
                        value: attr.btnUrl,
                        onChange: function(val) { props.setAttributes({ btnUrl: val }); }
                    }),
                    el(ToggleControl, {
                        label: 'Active by default (expands in block layout)',
                        checked: attr.isActive,
                        onChange: function(val) { props.setAttributes({ isActive: val }); }
                    })
                )
            );

            var classes = 'accordion-option' + (attr.isActive ? ' active' : '');
            var style = { '--bg': 'url(' + attr.imageUrl + ')' };
            
            return [
                inspector,
                el('div', { className: classes, style: style },
                    el('div', { className: 'accordion-shadow' }),
                    el('div', { className: 'accordion-label' },
                        el('div', { className: 'accordion-info' },
                            el(RichText, {
                                tagName: 'div',
                                className: 'accordion-main',
                                value: attr.title,
                                onChange: function(val) { props.setAttributes({ title: val }); },
                                placeholder: 'Enter Title...',
                                keepPlaceholderOnFocus: true
                            }),
                            el('div', { className: 'accordion-reveal' },
                                el(RichText, {
                                    tagName: 'p',
                                    className: 'accordion-copy',
                                    value: attr.text,
                                    onChange: function(val) { props.setAttributes({ text: val }); },
                                    placeholder: 'Enter description text...',
                                    keepPlaceholderOnFocus: true
                                }),
                                el(RichText, {
                                    tagName: 'span',
                                    className: 'btn btn--primary',
                                    value: attr.btnText,
                                    onChange: function(val) { props.setAttributes({ btnText: val }); },
                                    placeholder: 'Button Text',
                                    keepPlaceholderOnFocus: true
                                })
                            )
                        )
                    )
                )
            ];
        },
        save: function(props) {
            var attr = props.attributes;
            var classes = 'accordion-option' + (attr.isActive ? ' active' : '');
            
            return el('div', { className: classes, style: { '--bg': 'url(' + attr.imageUrl + ')' }, 'data-url': attr.btnUrl || '' },
                el('div', { className: 'accordion-shadow' }),
                el('div', { className: 'accordion-label' },
                    el('div', { className: 'accordion-info' },
                        el(RichText.Content, { tagName: 'div', className: 'accordion-main', value: attr.title }),
                        el('div', { className: 'accordion-reveal' },
                            el(RichText.Content, { tagName: 'p', className: 'accordion-copy', value: attr.text }),
                            attr.btnText && el(RichText.Content, { tagName: 'a', href: attr.btnUrl || '#', className: 'btn btn--primary', value: attr.btnText })
                        )
                    )
                )
            );
        }
    });

    // 2. Action Banner Link (Glassmorphic Link Banner)
    blocks.registerBlockType('e3es/action-banner-link', {
        title: 'E3 Action Banner Link',
        icon: 'button',
        category: 'layout',
        attributes: {
            highlight: { type: 'string', default: 'The Design+Build Advantage' },
            text: { type: 'string', default: 'Learn how we simplify facility upgrades with single-source accountability.' },
            url: { type: 'string', default: '' }
        },
        edit: function(props) {
            var attr = props.attributes;
            
            var inspector = el(InspectorControls, {},
                el(PanelBody, { title: 'Banner Settings', initialOpen: true },
                    el(TextControl, {
                        label: 'Highlight Heading Text',
                        value: attr.highlight,
                        onChange: function(val) { props.setAttributes({ highlight: val }); }
                    }),
                    el(TextareaControl, {
                        label: 'Description Text',
                        value: attr.text,
                        onChange: function(val) { props.setAttributes({ text: val }); }
                    }),
                    el(TextControl, {
                        label: 'Link URL',
                        value: attr.url,
                        onChange: function(val) { props.setAttributes({ url: val }); }
                    })
                )
            );

            return [
                inspector,
                el('div', { className: 'verticals-hero__db-action' },
                    el('div', { className: 'db-banner-link' },
                        el('div', { className: 'db-banner-link__content' },
                            el(RichText, {
                                tagName: 'span',
                                className: 'db-banner-link__highlight',
                                value: attr.highlight,
                                onChange: function(val) { props.setAttributes({ highlight: val }); },
                                placeholder: 'Highlight Text'
                            }),
                            el(RichText, {
                                tagName: 'span',
                                className: 'db-banner-link__text',
                                value: attr.text,
                                onChange: function(val) { props.setAttributes({ text: val }); },
                                placeholder: 'Description Text'
                            })
                        )
                    )
                )
            ];
        },
        save: function(props) {
            var attr = props.attributes;
            return el('div', { className: 'verticals-hero__db-action' },
                el('a', { href: attr.url || '#', className: 'db-banner-link' },
                    el('div', { className: 'db-banner-link__content' },
                        el(RichText.Content, { tagName: 'span', className: 'db-banner-link__highlight', value: attr.highlight }),
                        el(RichText.Content, { tagName: 'span', className: 'db-banner-link__text', value: attr.text })
                    )
                )
            );
        }
    });

    // 2b. E3 Section Icon — self-hosted FA SVG, no CDN dependency
    blocks.registerBlockType('e3es/section-icon', {
        title: 'E3 Section Icon',
        icon: 'star-filled',
        category: 'layout',
        attributes: {
            icon:   { type: 'string', default: '' },
            size:   { type: 'string', default: 'md' },
            color:  { type: 'string', default: 'green' },
            layout: { type: 'string', default: 'above' }
        },
        edit: function(props) {
            var attr  = props.attributes;
            var icons = window.E3_FA_ICONS || {};
            var searchState = element.useState('');
            var search = searchState[0]; var setSearch = searchState[1];

            var filtered = Object.keys(icons).filter(function(k) {
                if (!search) return true;
                var s = search.toLowerCase();
                return k.includes(s) || ((icons[k].label || '').toLowerCase().includes(s));
            });

            var szPx = { sm: 32, md: 48, lg: 64 }[attr.size] || 48;
            var clrMap = { green: 'var(--color-primary-green,#5c8a1e)', dark: '#1a2a1e', white: '#ffffff', current: 'currentColor' };
            var clr  = clrMap[attr.color] || 'currentColor';

            function mkSvg(k, sz, col) {
                var d = icons[k]; if (!d) return null;
                return el('svg', {
                    xmlns: 'http://www.w3.org/2000/svg',
                    viewBox: '0 0 ' + d.w + ' ' + d.h,
                    width: sz, height: sz,
                    fill: col || 'currentColor',
                    'aria-hidden': true,
                    style: { display: 'block', flexShrink: 0 }
                }, d.paths.map(function(p, i) { return el('path', { key: i, d: p }); }));
            }

            // ── All controls in sidebar ───────────────────────────────────
            var inspector = el(InspectorControls, null,
                el(PanelBody, { title: 'Icon', initialOpen: true },
                    el(TextControl, {
                        label: 'Search icons',
                        value: search,
                        onChange: setSearch,
                        placeholder: 'Filter by name…'
                    }),
                    el('div', {
                        style: {
                            display: 'grid', gridTemplateColumns: 'repeat(6,1fr)', gap: '4px',
                            maxHeight: '200px', overflowY: 'auto',
                            border: '1px solid #ddd', borderRadius: '4px',
                            padding: '4px', background: '#fafafa', marginBottom: '8px'
                        }
                    }, filtered.map(function(k) {
                        var sel = attr.icon === k;
                        return el('button', {
                            key: k, type: 'button',
                            title: (icons[k] || {}).label || k,
                            onClick: function() { props.setAttributes({ icon: k }); },
                            style: {
                                padding: '6px', cursor: 'pointer',
                                background: sel ? '#5c8a1e' : 'transparent',
                                border: sel ? '2px solid #5c8a1e' : '2px solid transparent',
                                borderRadius: '3px', display: 'flex',
                                alignItems: 'center', justifyContent: 'center'
                            }
                        }, mkSvg(k, 18, sel ? '#fff' : '#555'));
                    })),
                    attr.icon && el('button', {
                        type: 'button',
                        onClick: function() { props.setAttributes({ icon: '' }); },
                        style: { fontSize: '12px', cursor: 'pointer', border: '1px solid #ccc', borderRadius: '3px', padding: '3px 8px', background: 'transparent' }
                    }, '✕ Clear selection')
                ),
                el(PanelBody, { title: 'Display', initialOpen: true },
                    el(SelectControl, {
                        label: 'Size', value: attr.size,
                        onChange: function(v) { props.setAttributes({ size: v }); },
                        options: [
                            { label: 'Small (32px)',  value: 'sm' },
                            { label: 'Medium (48px)', value: 'md' },
                            { label: 'Large (64px)',  value: 'lg' }
                        ]
                    }),
                    el(SelectControl, {
                        label: 'Color', value: attr.color,
                        onChange: function(v) { props.setAttributes({ color: v }); },
                        options: [
                            { label: 'Green (Brand)', value: 'green'   },
                            { label: 'Dark',          value: 'dark'    },
                            { label: 'White',         value: 'white'   },
                            { label: 'Inherit',       value: 'current' }
                        ]
                    }),
                    el(SelectControl, {
                        label: 'Layout', value: attr.layout,
                        onChange: function(v) { props.setAttributes({ layout: v }); },
                        options: [
                            { label: 'Above title (stacked)', value: 'above' },
                            { label: 'Left of title (inline)', value: 'left'  }
                        ]
                    })
                )
            );

            // ── Canvas: matches final rendered output exactly ─────────────
            var layoutClass = attr.layout === 'left' ? ' e3es-icon--layout-left' : '';
            var canvas = attr.icon
                ? el('div', {
                    className: 'e3es-icon e3es-icon--' + attr.size + ' e3es-icon--' + attr.color + layoutClass,
                    style: { color: clr, display: 'inline-flex' }
                  }, mkSvg(attr.icon, szPx, clr))
                : el('div', {
                    style: {
                        display: 'inline-flex', alignItems: 'center', gap: '8px',
                        padding: '8px 12px', border: '1px dashed #bbb', borderRadius: '4px',
                        color: '#999', fontSize: '12px', background: '#f8f8f8'
                    }
                  },
                    el('svg', { xmlns: 'http://www.w3.org/2000/svg', viewBox: '0 0 576 512', width: 18, height: 18, fill: '#bbb', style: { display: 'block' } },
                        el('path', { d: 'M316.9 18C311.6 7 300.4 0 288.1 0s-23.4 7-28.8 18L195 150.3 51.4 171.5c-12 1.8-22 10.2-25.7 21.7s-.7 24.2 7.9 32.7L137.8 329 113.2 474.7c-2 12 3 24.2 12.9 31.3s23 8 33.8 2.3l128.3-68.5 128.3 68.5c10.8 5.7 23.9 4.4 33.8-2.3s14.9-19.3 12.9-31.3L438.5 329 542.7 225.9c8.6-8.5 11.7-21.2 7.9-32.7s-13.7-19.9-25.7-21.7L381.2 150.3 316.9 18z' })
                    ),
                    'Choose icon in Block Options'
                  );

            return [ inspector, canvas ];
        },
        save: function(props) {
            var attr  = props.attributes;
            var icons = window.E3_FA_ICONS || {};
            if (!attr.icon || !icons[attr.icon]) return null;
            var d = icons[attr.icon];
            var cls = 'e3es-icon e3es-icon--' + attr.size + ' e3es-icon--' + attr.color;
            if (attr.layout === 'left') cls += ' e3es-icon--layout-left';
            return el('div', { className: cls },
                el('svg', { xmlns: 'http://www.w3.org/2000/svg', viewBox: '0 0 ' + d.w + ' ' + d.h, 'aria-hidden': true, focusable: false },
                    d.paths.map(function(p, i) { return el('path', { key: i, d: p, fill: 'currentColor' }); })
                )
            );
        }
    });

    // 3. Two-Column Feature block — LEGACY (original architecture)
    //    Keeps the wp:e3es/two-column name so all existing pages recover cleanly.
    //    Inner blocks live inside db-feature__content; image is a static img tag.
    blocks.registerBlockType('e3es/two-column', {
        title: 'E3 Skewed Two Column',
        icon: 'columns',
        category: 'layout',
        attributes: {
            bgStyle:         { type: 'string',  default: 'white' },
            reverse:         { type: 'boolean', default: false   },
            mapSpill:        { type: 'boolean', default: false   },
            listLabel:       { type: 'string',  default: ''      },
            imageUrl:        { type: 'string',  default: ''      },
            imageAlt:        { type: 'string',  default: ''      },
            icon:            { type: 'string',  default: ''      },
            overlayHeadline: { type: 'string',  default: ''      },
            overlayText:     { type: 'string',  default: ''      },
            overlayBtnText:  { type: 'string',  default: ''      },
            overlayBtnUrl:   { type: 'string',  default: ''      }
        },
        __experimentalLabel: function(attributes) {
            return attributes.listLabel ? '2C: ' + attributes.listLabel : 'E3 Skewed Two Column';
        },
        edit: function(props) {
            var attr = props.attributes;

            // Sync first heading text -> listLabel so List View is readable
            var innerBlocks = wp.data.useSelect(function(select) {
                return select('core/block-editor').getBlock(props.clientId)
                    ? select('core/block-editor').getBlock(props.clientId).innerBlocks : [];
            }, [props.clientId]);

            element.useEffect(function() {
                function findFirstHeading(blocks) {
                    for (var i = 0; i < blocks.length; i++) {
                        var b = blocks[i];
                        if (b.name === 'core/heading' && b.attributes && b.attributes.content)
                            return b.attributes.content.replace(/<[^>]+>/g, '').trim();
                        if (b.innerBlocks && b.innerBlocks.length) {
                            var found = findFirstHeading(b.innerBlocks);
                            if (found) return found;
                        }
                    }
                    return '';
                }
                var t = findFirstHeading(innerBlocks || []);
                if (t !== attr.listLabel) props.setAttributes({ listLabel: t });
            }, [innerBlocks]);

            var inspector = el(InspectorControls, null,
                el(PanelBody, { title: 'Layout & Style', initialOpen: true },
                    el(SelectControl, {
                        label: 'Background',
                        value: attr.bgStyle,
                        options: [
                            { label: 'White',      value: 'white' },
                            { label: 'Light Grey', value: 'grey'  },
                            { label: 'Dark Green', value: 'green' }
                        ],
                        onChange: function(v) { props.setAttributes({ bgStyle: v }); }
                    }),
                    el(ToggleControl, {
                        label: 'Reverse layout (image on left)',
                        checked: attr.reverse,
                        onChange: function(v) { props.setAttributes({ reverse: v }); }
                    }),
                    el(ToggleControl, {
                        label: 'Map spill (image overflows container)',
                        checked: attr.mapSpill,
                        onChange: function(v) { props.setAttributes({ mapSpill: v }); }
                    })
                ),
                el(PanelBody, { title: 'Section Image', initialOpen: true },
                    el('div', { style: { marginBottom: '10px' } },
                        el(MediaSelect, {
                            url: attr.imageUrl,
                            onSelect: function(media) {
                                props.setAttributes({ imageUrl: media.url, imageAlt: media.alt || '' });
                            }
                        })
                    ),
                    attr.imageUrl && el(TextControl, {
                        label: 'Image Alt Text',
                        value: attr.imageAlt,
                        onChange: function(v) { props.setAttributes({ imageAlt: v }); }
                    }),
                    attr.imageUrl && el('button', {
                        className: 'button button-small',
                        style: { marginTop: '8px' },
                        onClick: function() { props.setAttributes({ imageUrl: '', imageAlt: '' }); }
                    }, 'Remove Image')
                ),
                el(PanelBody, { title: 'Image Hover Overlay', initialOpen: true },
                    el('p', { style: { fontSize: '12px', color: '#757575', margin: '0 0 12px 0', lineHeight: '1.4' } },
                        'Dark curtain shown when hovering the image. Leave blank to disable.'
                    ),
                    el(TextControl, {
                        label: 'Headline',
                        value: attr.overlayHeadline,
                        placeholder: 'e.g. Get in Touch',
                        onChange: function(v) { props.setAttributes({ overlayHeadline: v }); }
                    }),
                    el(TextareaControl, {
                        label: 'Body Text',
                        value: attr.overlayText,
                        placeholder: 'e.g. Our regional engineers are ready to help.',
                        rows: 3,
                        onChange: function(v) { props.setAttributes({ overlayText: v }); }
                    }),
                    el(TextControl, {
                        label: 'Button Label',
                        value: attr.overlayBtnText,
                        placeholder: 'e.g. Contact Us',
                        onChange: function(v) { props.setAttributes({ overlayBtnText: v }); }
                    }),
                    el(TextControl, {
                        label: 'Button URL',
                        value: attr.overlayBtnUrl,
                        placeholder: '/about-us/contact',
                        onChange: function(v) { props.setAttributes({ overlayBtnUrl: v }); }
                    })
                )
            );

            var sectionClass = 'db-feature db-feature--' + attr.bgStyle + (attr.mapSpill ? ' db-feature--map-spill' : '');
            var containerClass = 'db-feature__container' + (attr.reverse ? ' db-feature__container--reverse' : '');

            return [
                inspector,
                el('section', { className: sectionClass },
                    el('div', { className: containerClass },
                        el('div', { className: 'db-feature__content' },
                        el(InnerBlocks, {
                            template: [
                                ['e3es/section-icon', {}],
                                ['core/heading', { level: 2, placeholder: 'Section heading…' }],
                                ['core/paragraph', { placeholder: 'Body content…' }],
                                ['core/buttons', {}, [
                                    ['core/button', { text: 'Learn More' }]
                                ]]
                            ],
                            templateLock: false
                        })
                        ),
                        attr.imageUrl
                            ? el('div', { className: 'db-feature__image-wrapper' },
                                el('img', { src: attr.imageUrl, alt: attr.imageAlt || '', className: 'db-feature__image', style: { display: 'block', width: '100%', objectFit: 'cover' } }),
                                (attr.overlayHeadline || attr.overlayText || attr.overlayBtnText)
                                    ? el('div', { className: 'db-feature__image-overlay' },
                                        el('div', { className: 'db-feature__overlay-content' },
                                            attr.overlayHeadline ? el('h3', { className: 'db-feature__overlay-headline' }, attr.overlayHeadline) : null,
                                            attr.overlayText     ? el('p',  { className: 'db-feature__overlay-text'     }, attr.overlayText)     : null,
                                            (attr.overlayBtnText && attr.overlayBtnUrl)
                                                ? el('a', { href: '#', className: 'btn btn--outline-white db-feature__overlay-button' }, attr.overlayBtnText)
                                                : null
                                        )
                                      )
                                    : null
                              )
                            : el('div', { className: 'db-feature__image-wrapper', style: { background: '#e9ecef', display: 'flex', alignItems: 'center', justifyContent: 'center', minHeight: '200px', color: '#aaa', fontSize: '13px' } }, 'Select an image in Block Options →')
                    )
                )
            ];
        },
        save: function(props) {
            var attr = props.attributes;
            var sectionClass = 'wp-block-e3es-two-column db-feature db-feature--' + attr.bgStyle + (attr.mapSpill ? ' db-feature--map-spill' : '');
            var containerClass = 'db-feature__container' + (attr.reverse ? ' db-feature__container--reverse' : '');
            var hasOverlay = attr.overlayHeadline || attr.overlayText || attr.overlayBtnText;
            return el('section', { className: sectionClass },
                el('div', { className: containerClass },
                    el('div', { className: 'db-feature__content' },
                        el('div', { className: 'db-feature__icon' }),
                        el(InnerBlocks.Content)
                    ),
                    attr.imageUrl
                        ? el('div', { className: 'db-feature__image-wrapper' },
                            el('img', { src: attr.imageUrl, alt: attr.imageAlt || '', className: 'db-feature__image' }),
                            hasOverlay
                                ? el('div', { className: 'db-feature__image-overlay' },
                                    el('div', { className: 'db-feature__overlay-content' },
                                        attr.overlayHeadline
                                            ? el('h3', { className: 'db-feature__overlay-headline' }, attr.overlayHeadline)
                                            : null,
                                        attr.overlayText
                                            ? el('p', { className: 'db-feature__overlay-text' }, attr.overlayText)
                                            : null,
                                        (attr.overlayBtnText && attr.overlayBtnUrl)
                                            ? el('a', {
                                                href: attr.overlayBtnUrl,
                                                className: 'btn btn--outline-white db-feature__overlay-button'
                                              }, attr.overlayBtnText)
                                            : null
                                    )
                                  )
                                : null
                          )
                        : null
                )
            );
        }
    });

    // 3b. Two-Column Gutenberg Cover block (new architecture: core/columns + core/cover)
    blocks.registerBlockType('e3es/two-column-cover', {
        title: 'E3 Skewed Two Column Gutenberg Cover',
        icon: 'columns',
        category: 'layout',
        attributes: {
            bgStyle:   { type: 'string',  default: 'white' },
            reverse:   { type: 'boolean', default: false   },
            mapSpill:  { type: 'boolean', default: false   },
            listLabel: { type: 'string',  default: ''      }
        },
        __experimentalLabel: function(attributes) {
            return attributes.listLabel ? '2CC: ' + attributes.listLabel : 'E3 Skewed Two Column Gutenberg Cover';
        },
        edit: function(props) {
            var attr = props.attributes;

            // Sync first heading text -> listLabel for List View
            var innerBlocks = wp.data.useSelect(function(select) {
                return select('core/block-editor').getBlock(props.clientId)
                    ? select('core/block-editor').getBlock(props.clientId).innerBlocks : [];
            }, [props.clientId]);

            element.useEffect(function() {
                function findFirstHeading(blocks) {
                    for (var i = 0; i < blocks.length; i++) {
                        var b = blocks[i];
                        if (b.name === 'core/heading' && b.attributes && b.attributes.content)
                            return b.attributes.content.replace(/<[^>]+>/g, '').trim();
                        if (b.innerBlocks && b.innerBlocks.length) {
                            var found = findFirstHeading(b.innerBlocks);
                            if (found) return found;
                        }
                    }
                    return '';
                }
                var t = findFirstHeading(innerBlocks || []);
                if (t !== attr.listLabel) props.setAttributes({ listLabel: t });
            }, [innerBlocks]);

            var inspector = el(InspectorControls, null,
                el(PanelBody, { title: 'Layout & Style', initialOpen: true },
                    el(SelectControl, {
                        label: 'Background',
                        value: attr.bgStyle,
                        options: [
                            { label: 'White',      value: 'white' },
                            { label: 'Light Grey', value: 'grey'  },
                            { label: 'Dark Green', value: 'green' }
                        ],
                        onChange: function(v) { props.setAttributes({ bgStyle: v }); }
                    }),
                    el(ToggleControl, {
                        label: 'Reverse layout (image on left)',
                        checked: attr.reverse,
                        onChange: function(v) { props.setAttributes({ reverse: v }); }
                    }),
                    el(ToggleControl, {
                        label: 'Map spill (image overflows container)',
                        checked: attr.mapSpill,
                        onChange: function(v) { props.setAttributes({ mapSpill: v }); }
                    })
                )
            );

            var sectionClass = 'db-feature db-feature--' + attr.bgStyle + (attr.mapSpill ? ' db-feature--map-spill' : '');
            var containerClass = 'db-feature__container' + (attr.reverse ? ' db-feature__container--reverse' : '');

            return [
                inspector,
                el('section', { className: sectionClass },
                    el('div', { className: containerClass },
                        el(InnerBlocks, {
                            template: [
                                ['core/columns', {}, [
                                    ['core/column', { className: 'db-feature__content-col', width: '55%' }, [
                                        ['e3es/section-icon', {}],
                                        ['core/heading', { level: 2, placeholder: 'Section heading…' }],
                                        ['core/heading', { level: 3, placeholder: 'Subheading…' }],
                                        ['core/paragraph', { placeholder: 'Body content…' }],
                                        ['core/buttons', {}, [
                                            ['core/button', { text: 'Learn More' }]
                                        ]]
                                    ]],
                                    ['core/column', { className: 'db-feature__image-col', width: '45%' }, [
                                        ['core/cover', {
                                            url: '/wp-content/uploads/2026/06/E3-background-layered-1-scaled.jpg',
                                            id: 990,
                                            alt: 'E3 Background Layered',
                                            dimRatio: 0,
                                            customOverlayColor: '#244823',
                                            isUserOverlayColor: false,
                                            minHeight: 450,
                                            minHeightUnit: 'px',
                                            contentPosition: 'bottom center',
                                            sizeSlug: 'full'
                                        }, [
                                            ['core/paragraph', { placeholder: 'Write title…', align: 'left', fontSize: 'large', content: 'Title' }],
                                            ['core/paragraph', { placeholder: 'Write description…', content: 'Details about this service...' }],
                                            ['core/buttons', {}, [['core/button', {}]]]
                                        ]]
                                    ]]
                                ]]
                            ],
                            templateLock: false
                        })
                    )
                )
            ];
        },
        save: function(props) {
            var attr = props.attributes;
            var sectionClass = 'wp-block-e3es-two-column-cover db-feature db-feature--' + attr.bgStyle + (attr.mapSpill ? ' db-feature--map-spill' : '');
            var containerClass = 'db-feature__container' + (attr.reverse ? ' db-feature__container--reverse' : '');
            return el('section', { className: sectionClass },
                el('div', { className: containerClass },
                    el(InnerBlocks.Content)
                )
            );
        }
    });



    // 4. Services Grid — dynamic block: manual picker or auto query
    blocks.registerBlockType('e3es/services-grid', {
        title: 'E3 Services Grid',
        icon: 'grid-view',
        category: 'layout',
        attributes: {
            mode:        { type: 'string',  default: 'manual' },
            selectedIds: { type: 'array',   default: [],  items: { type: 'integer' } },
            parentId:    { type: 'integer', default: 0 },
            limit:       { type: 'integer', default: 4 },
            orderBy:     { type: 'string',  default: 'menu_order' }
        },
        edit: function(props) {
            var attr = props.attributes;
            var useState = element.useState;
            var useEffect = element.useEffect;
            var RangeControl = components.RangeControl;
            var CheckboxControl = components.CheckboxControl;
            var ServerSideRender = wp.serverSideRender;

            var postsState = useState([]);
            var posts = postsState[0]; var setPosts = postsState[1];
            var loadingState = useState(true);
            var loading = loadingState[0]; var setLoading = loadingState[1];
            var searchState = useState('');
            var search = searchState[0]; var setSearch = searchState[1];

            // Fetch all services for picker
            useEffect(function() {
                wp.apiFetch({ path: '/e3es/v1/services/list' }).then(function(data) {
                    setPosts(data);
                    setLoading(false);
                }).catch(function() { setLoading(false); });
            }, []);

            // Build parent options from posts
            var parentOptions = [{ label: '— All Services —', value: 0 }];
            var seen = {};
            posts.forEach(function(p) {
                if (p.parent === 0 && !seen[p.id]) {
                    seen[p.id] = true;
                    parentOptions.push({ label: p.title, value: p.id });
                }
            });

            function toggleService(id) {
                var current = (attr.selectedIds || []).slice();
                var idx = current.indexOf(id);
                if (idx > -1) {
                    current.splice(idx, 1);
                } else {
                    current.push(id);
                }
                props.setAttributes({ selectedIds: current });
            }

            var filteredPosts = posts;
            if (search) {
                var q = search.toLowerCase();
                filteredPosts = posts.filter(function(p) {
                    return p.title.toLowerCase().indexOf(q) > -1;
                });
            }

            var inspector = el(InspectorControls, null,
                el(PanelBody, { title: 'Display Mode', initialOpen: true },
                    el(SelectControl, {
                        label: 'Source',
                        value: attr.mode,
                        options: [
                            { label: 'Manual — pick specific services', value: 'manual' },
                            { label: 'Automatic — query by parent/order', value: 'auto' }
                        ],
                        onChange: function(v) { props.setAttributes({ mode: v }); }
                    })
                ),
                attr.mode === 'manual' && el(PanelBody, { title: 'Select Services', initialOpen: true },
                    el(TextControl, {
                        label: 'Search',
                        value: search,
                        onChange: setSearch,
                        placeholder: 'Filter services…'
                    }),
                    loading
                        ? el('p', null, 'Loading services…')
                        : el('div', { style: { maxHeight: '300px', overflowY: 'auto' } },
                            filteredPosts.map(function(p) {
                                var isSelected = (attr.selectedIds || []).indexOf(p.id) > -1;
                                return el('div', { key: p.id, style: { display: 'flex', alignItems: 'center', gap: '8px', padding: '4px 0', borderBottom: '1px solid #eee' } },
                                    el(CheckboxControl, {
                                        checked: isSelected,
                                        onChange: function() { toggleService(p.id); },
                                        __nextHasNoMarginBottom: true
                                    }),
                                    p.thumbnail && el('img', { src: p.thumbnail, style: { width: '32px', height: '32px', objectFit: 'cover', borderRadius: '3px', flexShrink: 0 } }),
                                    el('span', { style: { fontSize: '13px' } },
                                        (p.parent ? '↳ ' : '') + p.title
                                    )
                                );
                            })
                          ),
                    (attr.selectedIds || []).length > 0 && el('p', { style: { marginTop: '8px', fontSize: '12px', color: '#757575' } },
                        (attr.selectedIds || []).length + ' selected — drag to reorder in List View'
                    )
                ),
                attr.mode === 'auto' && el(PanelBody, { title: 'Auto Settings', initialOpen: true },
                    el(SelectControl, {
                        label: 'Filter by Parent Service',
                        value: attr.parentId,
                        options: parentOptions,
                        onChange: function(v) { props.setAttributes({ parentId: parseInt(v, 10) }); }
                    }),
                    el(RangeControl, {
                        label: 'Number of services',
                        value: attr.limit,
                        min: 1,
                        max: 12,
                        onChange: function(v) { props.setAttributes({ limit: v }); }
                    }),
                    el(SelectControl, {
                        label: 'Order By',
                        value: attr.orderBy,
                        options: [
                            { label: 'Menu Order', value: 'menu_order' },
                            { label: 'Title A-Z',  value: 'title' },
                            { label: 'Date',        value: 'date' }
                        ],
                        onChange: function(v) { props.setAttributes({ orderBy: v }); }
                    })
                )
            );

            return [
                inspector,
                el('div', { className: 'services services--editor' },
                    el(ServerSideRender, {
                        block: 'e3es/services-grid',
                        attributes: attr,
                        EmptyResponsePlaceholder: function() {
                            return el('div', { style: { padding: '2rem', textAlign: 'center', color: '#999', background: '#f9f9f9', border: '2px dashed #ddd' } },
                                attr.mode === 'manual'
                                    ? 'Select services in the sidebar →'
                                    : 'No services found for this filter.'
                            );
                        }
                    })
                )
            ];
        },
        save: function() { return null; }
    });

    // 4b. Services Card — kept as DEPRECATED for block recovery of old content
    blocks.registerBlockType('e3es/services-card', {
        title: 'E3 Services Card (Legacy)',
        icon: 'format-image',
        category: 'layout',
        parent: ['e3es/services-grid'],
        attributes: {
            title: { type: 'string', default: '' },
            text: { type: 'string', default: '' },
            imageUrl: { type: 'string', default: '' },
            imageAlt: { type: 'string', default: '' },
            linkUrl: { type: 'string', default: '' },
            focalPointX: { type: 'number', default: 0.5 },
            focalPointY: { type: 'number', default: 0.5 }
        },
        edit: function(props) {
            return el('div', { style: { padding: '1rem', background: '#fff3cd', border: '1px solid #ffc107', borderRadius: '4px' } },
                el('p', { style: { margin: 0, fontWeight: 'bold' } }, '⚠️ Legacy Services Card'),
                el('p', { style: { margin: '4px 0 0', fontSize: '13px' } }, 'This block is deprecated. Delete this block and use the new E3 Services Grid instead.')
            );
        },
        save: function(props) {
            var attr = props.attributes;
            var cardContent = [
                attr.imageUrl && el('img', { src: attr.imageUrl, alt: attr.imageAlt, className: 'services__card-image' }),
                el('div', { className: 'services__card-content' },
                    el(RichText.Content, { tagName: 'h3', className: 'services__card-title', value: attr.title }),
                    el(RichText.Content, { tagName: 'p', className: 'services__card-text', value: attr.text })
                )
            ];
            if (attr.linkUrl) {
                return el('div', { className: 'services__card' },
                    el('a', { href: attr.linkUrl, className: 'services__card-link', style: { textDecoration: 'none', color: 'inherit' } }, cardContent)
                );
            }
            return el('div', { className: 'services__card' }, cardContent);
        }
    });

    // 4c. Clients Grid — dynamic block: manual picker or auto taxonomy query
    blocks.registerBlockType('e3es/clients-grid', {
        title: 'E3 Clients',
        icon: 'portfolio',
        category: 'layout',
        attributes: {
            mode:        { type: 'string',  default: 'manual' },
            selectedIds: { type: 'array',   default: [],  items: { type: 'integer' } },
            taxonomy:    { type: 'string',  default: '' },
            termSlug:    { type: 'string',  default: '' },
            limit:       { type: 'integer', default: 4 },
            orderBy:     { type: 'string',  default: 'title' }
        },
        edit: function(props) {
            var attr = props.attributes;
            var useState = element.useState;
            var useEffect = element.useEffect;
            var RangeControl = components.RangeControl;
            var CheckboxControl = components.CheckboxControl;
            var ServerSideRender = wp.serverSideRender;

            var postsState = useState([]);
            var posts = postsState[0]; var setPosts = postsState[1];
            var loadingState = useState(true);
            var loading = loadingState[0]; var setLoading = loadingState[1];
            var searchState = useState('');
            var search = searchState[0]; var setSearch = searchState[1];

            var taxState = useState(null);
            var taxData = taxState[0]; var setTaxData = taxState[1];

            // Fetch all clients for picker
            useEffect(function() {
                wp.apiFetch({ path: '/e3es/v1/clients/list' }).then(function(data) {
                    setPosts(data);
                    setLoading(false);
                }).catch(function() { setLoading(false); });
            }, []);

            // Fetch taxonomy terms for auto mode
            useEffect(function() {
                wp.apiFetch({ path: '/e3es/v1/clients/taxonomies' }).then(function(data) {
                    setTaxData(data);
                }).catch(function() { setTaxData({}); });
            }, []);

            function toggleClient(id) {
                var current = (attr.selectedIds || []).slice();
                var idx = current.indexOf(id);
                if (idx > -1) {
                    current.splice(idx, 1);
                } else {
                    current.push(id);
                }
                props.setAttributes({ selectedIds: current });
            }

            var filteredPosts = posts;
            if (search) {
                var q = search.toLowerCase();
                filteredPosts = posts.filter(function(p) {
                    return p.title.toLowerCase().indexOf(q) > -1;
                });
            }

            // Build taxonomy options
            var taxonomyOptions = [{ label: '— Select Taxonomy —', value: '' }];
            if (taxData) {
                Object.keys(taxData).forEach(function(slug) {
                    var labels = { 'industry': 'Industry', 'region': 'Region', 'client-services': 'Services Provided' };
                    taxonomyOptions.push({ label: labels[slug] || slug, value: slug });
                });
            }

            // Build term options for selected taxonomy
            var termOptions = [{ label: '— All —', value: '' }];
            if (attr.taxonomy && taxData && taxData[attr.taxonomy]) {
                taxData[attr.taxonomy].forEach(function(t) {
                    termOptions.push({ label: t.name + ' (' + t.count + ')', value: t.slug });
                });
            }

            var inspector = el(InspectorControls, null,
                el(PanelBody, { title: 'Display Mode', initialOpen: true },
                    el(SelectControl, {
                        label: 'Source',
                        value: attr.mode,
                        options: [
                            { label: 'Manual — pick specific clients', value: 'manual' },
                            { label: 'Automatic — query by taxonomy', value: 'auto' }
                        ],
                        onChange: function(v) { props.setAttributes({ mode: v }); }
                    })
                ),
                attr.mode === 'manual' && el(PanelBody, { title: 'Select Clients', initialOpen: true },
                    el(TextControl, {
                        label: 'Search',
                        value: search,
                        onChange: setSearch,
                        placeholder: 'Filter clients…'
                    }),
                    loading
                        ? el('p', null, 'Loading clients…')
                        : el('div', { style: { maxHeight: '300px', overflowY: 'auto' } },
                            filteredPosts.map(function(p) {
                                var isSelected = (attr.selectedIds || []).indexOf(p.id) > -1;
                                return el('div', { key: p.id, style: { display: 'flex', alignItems: 'center', gap: '8px', padding: '4px 0', borderBottom: '1px solid #eee' } },
                                    el(CheckboxControl, {
                                        checked: isSelected,
                                        onChange: function() { toggleClient(p.id); },
                                        __nextHasNoMarginBottom: true
                                    }),
                                    p.thumbnail && el('img', { src: p.thumbnail, style: { width: '32px', height: '32px', objectFit: 'cover', borderRadius: '3px', flexShrink: 0 } }),
                                    el('span', { style: { fontSize: '13px' } }, p.title)
                                );
                            })
                          ),
                    (attr.selectedIds || []).length > 0 && el('p', { style: { marginTop: '8px', fontSize: '12px', color: '#757575' } },
                        (attr.selectedIds || []).length + ' selected'
                    )
                ),
                attr.mode === 'auto' && el(PanelBody, { title: 'Auto Settings', initialOpen: true },
                    el(SelectControl, {
                        label: 'Filter by Taxonomy',
                        value: attr.taxonomy,
                        options: taxonomyOptions,
                        onChange: function(v) { props.setAttributes({ taxonomy: v, termSlug: '' }); }
                    }),
                    attr.taxonomy && el(SelectControl, {
                        label: 'Filter by Term',
                        value: attr.termSlug,
                        options: termOptions,
                        onChange: function(v) { props.setAttributes({ termSlug: v }); }
                    }),
                    el(RangeControl, {
                        label: 'Number of clients',
                        value: attr.limit,
                        min: 1,
                        max: 24,
                        onChange: function(v) { props.setAttributes({ limit: v }); }
                    }),
                    el(SelectControl, {
                        label: 'Order By',
                        value: attr.orderBy,
                        options: [
                            { label: 'Title A-Z', value: 'title' },
                            { label: 'Date',      value: 'date' }
                        ],
                        onChange: function(v) { props.setAttributes({ orderBy: v }); }
                    })
                )
            );

            return [
                inspector,
                el('div', { className: 'clients-grid clients-grid--editor' },
                    el(ServerSideRender, {
                        block: 'e3es/clients-grid',
                        attributes: attr,
                        EmptyResponsePlaceholder: function() {
                            return el('div', { style: { padding: '2rem', textAlign: 'center', color: '#999', background: '#f9f9f9', border: '2px dashed #ddd' } },
                                attr.mode === 'manual'
                                    ? 'Select clients in the sidebar →'
                                    : 'No clients found for this filter.'
                            );
                        }
                    })
                )
            ];
        },
        save: function() { return null; }
    });

    // 4d. Client Finder Dynamic Block with Filters & Map
    blocks.registerBlockType('e3es/client-finder', {
        title: 'E3 Client Finder & Map',
        icon: 'location',
        category: 'layout',
        attributes: {
            onlyShowFeatured:   { type: 'boolean', default: true },
            showRegionFilter:   { type: 'boolean', default: true },
            showIndustryFilter: { type: 'boolean', default: true },
            showServiceFilter:  { type: 'boolean', default: true },
            showSearchFilter:   { type: 'boolean', default: true },
            showMap:            { type: 'boolean', default: true },
            showCardTags:       { type: 'boolean', default: true }
        },
        edit: function(props) {
            var attr = props.attributes;
            var ToggleControl = components.ToggleControl;
            var ServerSideRender = wp.serverSideRender;

            var inspector = el(InspectorControls, null,
                el(PanelBody, { title: 'Filter Options', initialOpen: true },
                    el(ToggleControl, {
                        label: 'Only Show Featured Clients',
                        checked: attr.onlyShowFeatured,
                        onChange: function(v) { props.setAttributes({ onlyShowFeatured: v }); }
                    }),
                    el(ToggleControl, {
                        label: 'Show Region Filter',
                        checked: attr.showRegionFilter,
                        onChange: function(v) { props.setAttributes({ showRegionFilter: v }); }
                    }),
                    el(ToggleControl, {
                        label: 'Show Industry Filter',
                        checked: attr.showIndustryFilter,
                        onChange: function(v) { props.setAttributes({ showIndustryFilter: v }); }
                    }),
                    el(ToggleControl, {
                        label: 'Show Service Filter',
                        checked: attr.showServiceFilter,
                        onChange: function(v) { props.setAttributes({ showServiceFilter: v }); }
                    }),
                    el(ToggleControl, {
                        label: 'Show Search Bar',
                        checked: attr.showSearchFilter,
                        onChange: function(v) { props.setAttributes({ showSearchFilter: v }); }
                    }),
                    el(ToggleControl, {
                        label: 'Show Texas Interactive Map',
                        checked: attr.showMap,
                        onChange: function(v) { props.setAttributes({ showMap: v }); }
                    }),
                    el(ToggleControl, {
                        label: 'Show Tags on Cards',
                        checked: attr.showCardTags,
                        onChange: function(v) { props.setAttributes({ showCardTags: v }); }
                    })
                )
            );

            return [
                inspector,
                el('div', { className: 'clients-finder-block-editor-preview' },
                    el(ServerSideRender, {
                        block: 'e3es/client-finder',
                        attributes: attr
                    })
                )
            ];
        },
        save: function() { return null; }
    });

    // 5. Design-Build Advantage Grid (Parent)
    blocks.registerBlockType('e3es/design-build-advantage', {
        title: 'E3 Design-Build Grid',
        icon: 'grid-view',
        category: 'layout',
        edit: function(props) {
            return el('div', { className: 'design-build__grid' },
                el(InnerBlocks, { allowedBlocks: ['e3es/design-build-card'] })
            );
        },
        save: function(props) {
            return el('div', { className: 'design-build__grid' },
                el(InnerBlocks.Content)
            );
        }
    });

    // 5b. Design-Build Card (Child)
    blocks.registerBlockType('e3es/design-build-card', {
        title: 'E3 Design-Build Card',
        icon: 'excerpt-view',
        category: 'layout',
        parent: ['e3es/design-build-advantage'],
        attributes: {
            title: { type: 'string', default: '' },
            text: { type: 'string', default: '' },
            number: { type: 'string', default: '1' },
            icon: { type: 'string', default: 'clock' } // clock, shield, dollar
        },
        edit: function(props) {
            var attr = props.attributes;
            
            var inspector = el(InspectorControls, {},
                el(PanelBody, { title: 'Card Settings', initialOpen: true },
                    el(SelectControl, {
                        label: 'Card Icon',
                        value: attr.icon,
                        options: [
                            { label: 'Clock (Speed)', value: 'clock' },
                            { label: 'Shield (Accountability)', value: 'shield' },
                            { label: 'Dollar (Budget)', value: 'dollar' }
                        ],
                        onChange: function(val) { props.setAttributes({ icon: val }); }
                    }),
                    el(TextControl, {
                        label: 'Card Number / Label',
                        value: attr.number,
                        onChange: function(val) { props.setAttributes({ number: val }); }
                    }),
                    el(TextControl, {
                        label: 'Title',
                        value: attr.title,
                        onChange: function(val) { props.setAttributes({ title: val }); }
                    }),
                    el(TextareaControl, {
                        label: 'Text Description',
                        value: attr.text,
                        onChange: function(val) { props.setAttributes({ text: val }); }
                    })
                )
            );
            
            var cardIconSvg = null;
            if (attr.icon === 'clock') {
                cardIconSvg = el('svg', { viewBox: '0 0 24 24', width: '36', height: '36', fill: 'none', stroke: 'var(--color-primary-green)', strokeWidth: '2', strokeLinecap: 'round', strokeLinejoin: 'round' },
                    el('circle', { cx: '12', cy: '12', r: '10' }),
                    el('polyline', { points: '12 6 12 12 16 14' })
                );
            } else if (attr.icon === 'shield') {
                cardIconSvg = el('svg', { viewBox: '0 0 24 24', width: '36', height: '36', fill: 'none', stroke: 'var(--color-primary-green)', strokeWidth: '2', strokeLinecap: 'round', strokeLinejoin: 'round' },
                    el('path', { d: 'M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z' })
                );
            } else if (attr.icon === 'dollar') {
                cardIconSvg = el('svg', { viewBox: '0 0 24 24', width: '36', height: '36', fill: 'none', stroke: 'var(--color-primary-green)', strokeWidth: '2', strokeLinecap: 'round', strokeLinejoin: 'round' },
                    el('line', { x1: '12', y1: '1', x2: '12', y2: '23' }),
                    el('path', { d: 'M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6' })
                );
            }

            return [
                inspector,
                el('div', { className: 'design-build__card' },
                    el('div', { className: 'design-build__icon', style: { marginBottom: '1rem' } }, cardIconSvg),
                    el(RichText, {
                        tagName: 'h3',
                        className: 'design-build__card-title',
                        value: attr.title,
                        onChange: function(val) { props.setAttributes({ title: val }); },
                        placeholder: 'Card Title'
                    }),
                    el(RichText, {
                        tagName: 'p',
                        className: 'design-build__card-text',
                        value: attr.text,
                        onChange: function(val) { props.setAttributes({ text: val }); },
                        placeholder: 'Card Text'
                    })
                )
            ];
        },
        save: function(props) {
            var attr = props.attributes;
            
            // Get card icon SVG representation
            var cardIconSvg = null;
            if (attr.icon === 'clock') {
                cardIconSvg = el('svg', { viewBox: '0 0 24 24', width: '36', height: '36', fill: 'none', stroke: 'var(--color-primary-green)', strokeWidth: '2', strokeLinecap: 'round', strokeLinejoin: 'round' },
                    el('circle', { cx: '12', cy: '12', r: '10' }),
                    el('polyline', { points: '12 6 12 12 16 14' })
                );
            } else if (attr.icon === 'shield') {
                cardIconSvg = el('svg', { viewBox: '0 0 24 24', width: '36', height: '36', fill: 'none', stroke: 'var(--color-primary-green)', strokeWidth: '2', strokeLinecap: 'round', strokeLinejoin: 'round' },
                    el('path', { d: 'M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z' })
                );
            } else if (attr.icon === 'dollar') {
                cardIconSvg = el('svg', { viewBox: '0 0 24 24', width: '36', height: '36', fill: 'none', stroke: 'var(--color-primary-green)', strokeWidth: '2', strokeLinecap: 'round', strokeLinejoin: 'round' },
                    el('line', { x1: '12', y1: '1', x2: '12', y2: '23' }),
                    el('path', { d: 'M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6' })
                );
            }

            return el('div', { className: 'design-build__card' },
                el('div', { className: 'design-build__icon', style: { marginBottom: '1rem' } }, cardIconSvg),
                el(RichText.Content, { tagName: 'h3', className: 'design-build__card-title', value: attr.title }),
                el(RichText.Content, { tagName: 'p', className: 'design-build__card-text', value: attr.text })
            );
        }
    });

    // 6. Core Pillars (Parent)
    blocks.registerBlockType('e3es/core-pillars', {
        title: 'E3 Core Pillars',
        icon: 'grid-view',
        category: 'layout',
        edit: function(props) {
            return el('section', { className: 'db-pillars', style: { backgroundColor: 'var(--color-bg-light)', padding: '5rem 2rem' } },
                el('div', { style: { maxWidth: '1200px', margin: '0 auto', display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(300px, 1fr))', gap: '3rem' } },
                    el(InnerBlocks, { allowedBlocks: ['e3es/core-pillar'] })
                )
            );
        },
        save: function(props) {
            return el('section', { className: 'db-pillars', style: { backgroundColor: 'var(--color-bg-light)', padding: '5rem 2rem' } },
                el('div', { style: { maxWidth: '1200px', margin: '0 auto', display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(300px, 1fr))', gap: '3rem' } },
                    el(InnerBlocks.Content)
                )
            );
        }
    });

    // 6b. Core Pillar (Child)
    blocks.registerBlockType('e3es/core-pillar', {
        title: 'E3 Core Pillar Item',
        icon: 'admin-appearance',
        category: 'layout',
        parent: ['e3es/core-pillars'],
        attributes: {
            title: { type: 'string', default: '' },
            text: { type: 'string', default: '' }
        },
        edit: function(props) {
            var attr = props.attributes;
            
            var inspector = el(InspectorControls, {},
                el(PanelBody, { title: 'Pillar Settings', initialOpen: true },
                    el(TextControl, {
                        label: 'Pillar Title',
                        value: attr.title,
                        onChange: function(val) { props.setAttributes({ title: val }); }
                    }),
                    el(TextareaControl, {
                        label: 'Pillar Text',
                        value: attr.text,
                        onChange: function(val) { props.setAttributes({ text: val }); }
                    })
                )
            );

            return [
                inspector,
                el('div', { style: { background: 'white', padding: '2.5rem', boxShadow: '0 10px 30px rgba(0,0,0,0.05)', borderTop: '4px solid var(--color-primary-green)' } },
                    el(RichText, {
                        tagName: 'h3',
                        style: { color: 'var(--color-primary-green)', fontSize: '1.25rem', marginBottom: '1rem', textTransform: 'uppercase', letterSpacing: '1px', lineHeight: '1.3' },
                        value: attr.title,
                        onChange: function(val) { props.setAttributes({ title: val }); },
                        placeholder: 'Pillar Title'
                    }),
                    el(RichText, {
                        tagName: 'p',
                        style: { marginBottom: '0' },
                        value: attr.text,
                        onChange: function(val) { props.setAttributes({ text: val }); },
                        placeholder: 'Pillar Text'
                    })
                )
            ];
        },
        save: function(props) {
            var attr = props.attributes;
            return el('div', { style: { background: 'white', padding: '2.5rem', boxShadow: '0 10px 30px rgba(0,0,0,0.05)', borderTop: '4px solid var(--color-primary-green)' } },
                el(RichText.Content, { tagName: 'h3', style: { color: 'var(--color-primary-green)', fontSize: '1.25rem', marginBottom: '1rem', textTransform: 'uppercase', letterSpacing: '1px', lineHeight: '1.3' }, value: attr.title }),
                el(RichText.Content, { tagName: 'p', style: { marginBottom: '0' }, value: attr.text })
            );
        }
    });

    // 7. Comparison Table (Parent)
    blocks.registerBlockType('e3es/comparison-table', {
        title: 'E3 Comparison Table',
        icon: 'editor-table',
        category: 'layout',
        attributes: {
            columnCount: { type: 'number', default: 3 },
            headers: { type: 'array', default: ['', 'Traditional', 'E3 Design + Build'] }
        },
        edit: function(props) {
            var attr = props.attributes;
            var columnCount = attr.columnCount || 3;
            var headers = attr.headers || ['', 'Traditional', 'E3 Design + Build'];

            // Adjust headers size if columnCount changed
            if (headers.length !== columnCount) {
                var newHeaders = headers.slice(0, columnCount);
                while (newHeaders.length < columnCount) {
                    newHeaders.push('');
                }
                props.setAttributes({ headers: newHeaders });
                headers = newHeaders;
            }

            var inspector = el(InspectorControls, {},
                el(PanelBody, { title: 'Table Settings', initialOpen: true },
                    el(components.RangeControl, {
                        label: 'Number of Columns',
                        value: columnCount,
                        onChange: function(val) {
                            var newHeaders = headers.slice(0, val);
                            while (newHeaders.length < val) {
                                newHeaders.push('');
                            }
                            props.setAttributes({ columnCount: val, headers: newHeaders });

                            // Actively update all child Comparison Row blocks
                            var children = wp.data.select('core/block-editor').getBlocks(props.clientId);
                            children.forEach(function(child) {
                                var childAttr = child.attributes;
                                var childCells = childAttr.cells || [];
                                if (childCells.length === 0) {
                                    childCells = [childAttr.feature || '', childAttr.traditional || '', childAttr.e3 || ''];
                                }
                                var newCells = childCells.slice(0, val);
                                while (newCells.length < val) {
                                    newCells.push('');
                                }
                                wp.data.dispatch('core/block-editor').updateBlockAttributes(child.clientId, { cells: newCells });
                            });
                        },
                        min: 2,
                        max: 5
                    })
                )
            );

            // Generate header th elements
            var headerCells = [];
            for (var i = 0; i < columnCount; i++) {
                (function(index) {
                    if (index === 0) {
                        headerCells.push(
                            el('th', { key: index, style: { width: '20%', border: 'none', background: 'transparent' } },
                                el(RichText, {
                                    tagName: 'div',
                                    value: headers[index] || '',
                                    onChange: function(val) {
                                        var newHeaders = headers.slice();
                                        newHeaders[index] = val;
                                        props.setAttributes({ headers: newHeaders });
                                    },
                                    placeholder: 'Feature'
                                })
                            )
                        );
                    } else {
                        var width = Math.floor(80 / (columnCount - 1)) + '%';
                        headerCells.push(
                            el('th', { key: index, style: { width: width } },
                                el(RichText, {
                                    tagName: 'div',
                                    value: headers[index] || '',
                                    onChange: function(val) {
                                        var newHeaders = headers.slice();
                                        newHeaders[index] = val;
                                        props.setAttributes({ headers: newHeaders });
                                    },
                                    placeholder: 'Column ' + index
                                })
                            )
                        );
                    }
                })(i);
            }

            return [
                inspector,
                el('section', { className: 'comparison-section' },
                    el('div', { className: 'comparison-container' },
                        el('table', { className: 'comparison-table' },
                            el('thead', null,
                                el('tr', null, headerCells)
                            ),
                            el('tbody', null,
                                el(InnerBlocks, { allowedBlocks: ['e3es/comparison-row'] })
                            )
                        )
                    )
                )
            ];
        },
        save: function(props) {
            var attr = props.attributes;
            var columnCount = attr.columnCount || 3;
            var headers = attr.headers || ['', 'Traditional', 'E3 Design + Build'];
            
            var headerCells = [];
            for (var i = 0; i < columnCount; i++) {
                var headerText = headers[i] || '';
                if (i === 0) {
                    headerCells.push(
                        el('th', { key: i, scope: 'col', style: { width: '20%', border: 'none', background: 'transparent' } },
                            el(RichText.Content, { value: headerText })
                        )
                    );
                } else {
                    var width = Math.floor(80 / (columnCount - 1)) + '%';
                    headerCells.push(
                        el('th', { key: i, scope: 'col', style: { width: width } },
                            el(RichText.Content, { value: headerText })
                        )
                    );
                }
            }

            return el('section', { className: 'comparison-section' },
                el('div', { className: 'comparison-container' },
                    el('table', { className: 'comparison-table' },
                        el('thead', null,
                            el('tr', null, headerCells)
                        ),
                        el('tbody', null,
                            el(InnerBlocks.Content)
                        )
                    )
                )
            );
        }
    });

    // 7b. Comparison Row (Child)
    blocks.registerBlockType('e3es/comparison-row', {
        title: 'E3 Comparison Row',
        icon: 'editor-justify',
        category: 'layout',
        parent: ['e3es/comparison-table'],
        attributes: {
            feature: { type: 'string', default: '' },
            traditional: { type: 'string', default: '' },
            e3: { type: 'string', default: '' },
            cells: { type: 'array', default: [] }
        },
        edit: function(props) {
            var attr = props.attributes;
            
            // Hook to get parent's columnCount reactively
            var columnCount = wp.data.useSelect(function(select) {
                var parentClientId = select('core/block-editor').getBlockParents(props.clientId).pop();
                if (!parentClientId) return 3;
                var parent = select('core/block-editor').getBlock(parentClientId);
                return parent ? parent.attributes.columnCount : 3;
            }, [props.clientId]) || 3;

            // Get cells and ensure correct size
            var cells = attr.cells || [];
            if (cells.length === 0) {
                // Initialize from old attributes for backward compatibility
                cells = [attr.feature || '', attr.traditional || '', attr.e3 || ''];
            }
            
            // Ensure cells length matches columnCount
            if (cells.length !== columnCount) {
                var newCells = cells.slice(0, columnCount);
                while (newCells.length < columnCount) {
                    newCells.push('');
                }
                // Update properties in next tick to avoid updating during render loop warning
                window.setTimeout(function() {
                    props.setAttributes({ cells: newCells });
                }, 0);
                cells = newCells;
            }

            var inspector = el(InspectorControls, {},
                el(PanelBody, { title: 'Row Settings', initialOpen: true },
                    el('p', null, 'Edit cell values directly in the table visual editor.')
                )
            );

            var cellElements = [];
            for (var i = 0; i < columnCount; i++) {
                (function(index) {
                    if (index === 0) {
                        cellElements.push(
                            el('th', { key: index, scope: 'row', style: { width: '20%' } },
                                el(RichText, {
                                    tagName: 'div',
                                    value: cells[index] || '',
                                    onChange: function(val) {
                                        var newCells = cells.slice();
                                        newCells[index] = val;
                                        var updateObj = { cells: newCells };
                                        if (index === 0) updateObj.feature = val;
                                        if (index === 1) updateObj.traditional = val;
                                        if (index === 2) updateObj.e3 = val;
                                        props.setAttributes(updateObj);
                                    },
                                    placeholder: 'Feature Name'
                                })
                            )
                        );
                    } else {
                        var placeholder = index === 1 ? 'Traditional' : (index === 2 ? 'E3 Advantage' : 'Value');
                        var width = Math.floor(80 / (columnCount - 1)) + '%';
                        cellElements.push(
                            el('td', { key: index, style: { width: width } },
                                el(RichText, {
                                    tagName: 'div',
                                    value: cells[index] || '',
                                    onChange: function(val) {
                                        var newCells = cells.slice();
                                        newCells[index] = val;
                                        var updateObj = { cells: newCells };
                                        if (index === 1) updateObj.traditional = val;
                                        if (index === 2) updateObj.e3 = val;
                                        props.setAttributes(updateObj);
                                    },
                                    placeholder: placeholder
                                })
                            )
                        );
                    }
                })(i);
            }

            return [
                inspector,
                cellElements
            ];
        },
        save: function(props) {
            var attr = props.attributes;
            var cells = attr.cells || [];
            
            if (cells.length === 0) {
                cells = [attr.feature || '', attr.traditional || '', attr.e3 || ''];
            }
            
            var cellElements = [];
            for (var i = 0; i < cells.length; i++) {
                if (i === 0) {
                    cellElements.push(
                        el('th', { key: i, scope: 'row' },
                            el(RichText.Content, { value: cells[i] })
                        )
                    );
                } else {
                    cellElements.push(
                        el('td', { key: i },
                            el(RichText.Content, { value: cells[i] })
                        )
                    );
                }
            }
            
            return el('tr', null, cellElements);
        }
    });

    // 8. CTA Banner
    blocks.registerBlockType('e3es/cta-banner', {
        title: 'E3 CTA Banner',
        icon: 'megaphone',
        category: 'layout',
        attributes: {
            title: { type: 'string', default: '' },
            text: { type: 'string', default: '' },
            btnText: { type: 'string', default: '' },
            btnUrl: { type: 'string', default: '' }
        },
        edit: function(props) {
            var attr = props.attributes;
            
            var inspector = el(InspectorControls, {},
                el(PanelBody, { title: 'CTA Settings', initialOpen: true },
                    el(TextControl, {
                        label: 'CTA Title',
                        value: attr.title,
                        onChange: function(val) { props.setAttributes({ title: val }); }
                    }),
                    el(TextareaControl, {
                        label: 'CTA Subtitle / Description',
                        value: attr.text,
                        onChange: function(val) { props.setAttributes({ text: val }); }
                    }),
                    el(TextControl, {
                        label: 'Button Text',
                        value: attr.btnText,
                        onChange: function(val) { props.setAttributes({ btnText: val }); }
                    }),
                    el(TextControl, {
                        label: 'Button Link URL',
                        value: attr.btnUrl,
                        onChange: function(val) { props.setAttributes({ btnUrl: val }); }
                    })
                )
            );

            return [
                inspector,
                el('section', { className: 'cta-banner' },
                    el('div', { className: 'cta-banner__container' },
                        el(RichText, {
                            tagName: 'h2',
                            className: 'cta-banner__title',
                            value: attr.title,
                            onChange: function(val) { props.setAttributes({ title: val }); },
                            placeholder: 'CTA Title'
                        }),
                        el(RichText, {
                            tagName: 'p',
                            className: 'cta-banner__text',
                            value: attr.text,
                            onChange: function(val) { props.setAttributes({ text: val }); },
                            placeholder: 'CTA Subtitle / Description'
                        }),
                        el(RichText, {
                            tagName: 'span',
                            className: 'btn btn--primary cta-banner__btn',
                            value: attr.btnText,
                            onChange: function(val) { props.setAttributes({ btnText: val }); },
                            placeholder: 'Button Text'
                        })
                    )
                )
            ];
        },
        save: function(props) {
            var attr = props.attributes;
            return el('section', { className: 'cta-banner' },
                el('div', { className: 'cta-banner__container' },
                    el(RichText.Content, { tagName: 'h2', className: 'cta-banner__title', value: attr.title }),
                    el(RichText.Content, { tagName: 'p', className: 'cta-banner__text', value: attr.text }),
                    attr.btnText && el(RichText.Content, { tagName: 'a', href: attr.btnUrl || '#', className: 'btn btn--primary cta-banner__btn', value: attr.btnText })
                )
            );
        }
    });

    // 9. Mini Testimonial Callout — supports Manual and Linked modes
    blocks.registerBlockType('e3es/mini-testimonial', {
        title: 'E3 Testimonial',
        icon: 'editor-quote',
        category: 'layout',
        attributes: {
            layout:        { type: 'string',  default: 'callout' },
            mode:          { type: 'string',  default: 'manual' },
            testimonialId: { type: 'number',  default: 0 },
            quote:         { type: 'string',  default: '' },
            cite:          { type: 'string',  default: '' },
            photoUrl:      { type: 'string',  default: '' },
            caseStudyUrl:  { type: 'string',  default: '' },
            caseStudyText: { type: 'string',  default: 'Read Case Study' },
            bgStyle:       { type: 'string',  default: 'white' }
        },
        deprecated: [
            {
                // Preserve old static save so existing posts don't break
                attributes: {
                    quote:    { type: 'string', default: '' },
                    cite:     { type: 'string', default: '' },
                    photoUrl: { type: 'string', default: '' }
                },
                save: function(props) {
                    var attr = props.attributes;
                    return el('div', { className: 'mini-testimonial' },
                        el(RichText.Content, { tagName: 'blockquote', value: attr.quote }),
                        el('div', { className: 'mini-testimonial__footer' },
                            attr.photoUrl && el('img', { src: attr.photoUrl, alt: '', className: 'mini-testimonial__photo' }),
                            el(RichText.Content, { tagName: 'cite', value: attr.cite })
                        )
                    );
                }
            }
        ],
        edit: function(props) {
            var attr = props.attributes;
            var useState = element.useState;
            var useEffect = element.useEffect;

            var filtersState = useState(null);
            var filters = filtersState[0]; var setFilters = filtersState[1];

            var searchState = useState('');
            var search = searchState[0]; var setSearch = searchState[1];

            var filterPersonState = useState('');
            var filterPerson = filterPersonState[0]; var setFilterPerson = filterPersonState[1];

            var filterClientState = useState('');
            var filterClient = filterClientState[0]; var setFilterClient = filterClientState[1];

            var filterServiceState = useState('');
            var filterService = filterServiceState[0]; var setFilterService = filterServiceState[1];

            var filterIndustryState = useState('');
            var filterIndustry = filterIndustryState[0]; var setFilterIndustry = filterIndustryState[1];

            var filterRegionState = useState('');
            var filterRegion = filterRegionState[0]; var setFilterRegion = filterRegionState[1];

            var filterKeywordState = useState('');
            var filterKeyword = filterKeywordState[0]; var setFilterKeyword = filterKeywordState[1];

            var resultsState = useState([]);
            var results = resultsState[0]; var setResults = resultsState[1];

            var loadingState = useState(false);
            var loading = loadingState[0]; var setLoading = loadingState[1];

            var selectedState = useState(null);
            var selected = selectedState[0]; var setSelected = selectedState[1];

            var debounceRef = element.useRef(null);

            // Load filter options once
            useEffect(function() {
                wp.apiFetch({ path: '/e3es/v1/testimonials/filters' }).then(function(data) {
                    setFilters(data);
                }).catch(function() { setFilters({}); });
            }, []);

            // On mount, if linked mode with testimonialId, load selected item
            useEffect(function() {
                if (attr.mode === 'linked' && attr.testimonialId) {
                    wp.apiFetch({ path: '/e3es/v1/testimonials/search' }).then(function(data) {
                        var match = data.find(function(t) { return t.id === attr.testimonialId; });
                        if (match) setSelected(match);
                    });
                }
            }, []);

            // Debounced search/filter when in linked mode
            useEffect(function() {
                if (attr.mode !== 'linked' || selected) return;
                if (debounceRef.current) clearTimeout(debounceRef.current);
                debounceRef.current = setTimeout(function() {
                    setLoading(true);
                    var params = [];
                    if (search)        params.push('search='    + encodeURIComponent(search));
                    if (filterPerson)  params.push('person_id=' + encodeURIComponent(filterPerson));
                    if (filterClient)  params.push('client_id=' + encodeURIComponent(filterClient));
                    if (filterService) params.push('service='   + encodeURIComponent(filterService));
                    if (filterIndustry) params.push('industry=' + encodeURIComponent(filterIndustry));
                    if (filterRegion)  params.push('region='    + encodeURIComponent(filterRegion));
                    if (filterKeyword) params.push('keyword='   + encodeURIComponent(filterKeyword));
                    var path = '/e3es/v1/testimonials/search' + (params.length ? '?' + params.join('&') : '');
                    wp.apiFetch({ path: path }).then(function(data) {
                        setResults(data);
                        setLoading(false);
                    }).catch(function() {
                        setResults([]);
                        setLoading(false);
                    });
                }, 350);
                return function() { if (debounceRef.current) clearTimeout(debounceRef.current); };
            }, [attr.mode, selected, search, filterPerson, filterClient, filterService, filterIndustry, filterRegion, filterKeyword]);

            function selectTestimonial(item) {
                props.setAttributes({ testimonialId: item.id });
                setSelected(item);
            }

            function clearSelection() {
                props.setAttributes({ testimonialId: 0 });
                setSelected(null);
                setSearch('');
                setFilterPerson(''); setFilterClient(''); setFilterService(''); setFilterIndustry('');
                setFilterRegion(''); setFilterKeyword('');
            }

            // ── Build filter dropdown options ────────────────────────────
            var personOptions  = [{ value: '', label: '— Any Person —' }];
            var clientOptions  = [{ value: '', label: '— Any Client —' }];
            var serviceOptions = [{ value: '', label: '— Any Service —' }];
            var industryOptions= [{ value: '', label: '— Any Industry —' }];
            var regionOptions  = [{ value: '', label: '— Any Region —' }];
            var keywordOptions = [{ value: '', label: '— Any Keyword —' }];

            if (filters) {
                (filters.people   || []).forEach(function(p) { personOptions.push({ value: String(p.id), label: p.label }); });
                (filters.clients  || []).forEach(function(c) { clientOptions.push({ value: String(c.id), label: c.label }); });
                (filters.service  || []).forEach(function(v) { serviceOptions.push({ value: v, label: v }); });
                (filters.industry || []).forEach(function(v) { industryOptions.push({ value: v, label: v }); });
                (filters.region   || []).forEach(function(v) { regionOptions.push({ value: v, label: v }); });
                (filters.keyword  || []).forEach(function(v) { keywordOptions.push({ value: v, label: v }); });
            }

            // ── Sidebar: all settings go here ───────────────────────────
            var inspector = el(InspectorControls, {},
                // Layout panel
                el(PanelBody, { title: 'Layout & Style', initialOpen: true },
                    el(SelectControl, {
                        label: 'Layout Style',
                        value: attr.layout || 'callout',
                        options: [
                            { label: 'Callout (Polygon Box)', value: 'callout' },
                            { label: 'Picker Style (Standard Card)', value: 'picker' },
                            { label: 'Full-Width Block', value: 'full-width' }
                        ],
                        onChange: function(val) { props.setAttributes({ layout: val }); }
                    }),
                    el(SelectControl, {
                        label: 'Source',
                        value: attr.mode,
                        options: [
                            { label: 'Manual — enter text directly', value: 'manual' },
                            { label: 'Linked — pick from Testimonials', value: 'linked' }
                        ],
                        onChange: function(val) {
                            props.setAttributes({ mode: val });
                            if (val === 'manual') {
                                props.setAttributes({ testimonialId: 0 });
                                setSelected(null);
                            }
                        }
                    })
                ),

                // Manual mode settings
                attr.mode === 'manual' && el(PanelBody, { title: 'Testimonial Content', initialOpen: true },
                    el(TextareaControl, {
                        label: 'Quote',
                        value: attr.quote,
                        onChange: function(val) { props.setAttributes({ quote: val }); }
                    }),
                    el(TextControl, {
                        label: 'Citation / Author Name',
                        value: attr.cite,
                        onChange: function(val) { props.setAttributes({ cite: val }); }
                    }),
                    el('div', { style: { marginTop: '15px' } },
                        el('label', { style: { display: 'block', marginBottom: '5px', fontWeight: 'bold' } }, 'Author Photo'),
                        el(MediaSelect, {
                            url: attr.photoUrl,
                            onSelect: function(media) { props.setAttributes({ photoUrl: media.url }); }
                        }),
                        attr.photoUrl && el(Button, {
                            isDestructive: true, isSmall: true, style: { marginTop: '8px' },
                            onClick: function() { props.setAttributes({ photoUrl: '' }); }
                        }, 'Remove Photo')
                    )
                ),

                // Linked mode settings
                attr.mode === 'linked' && el(PanelBody, { title: 'Select Testimonial', initialOpen: true },
                    selected
                        ? el('div', {},
                            el('div', { style: { marginBottom: '12px', padding: '10px', background: '#f0f7e6', borderRadius: '4px', borderLeft: '3px solid var(--color-primary-green, #5c8a1e)' } },
                                el('strong', { style: { display: 'block', fontSize: '13px', marginBottom: '4px' } }, selected.personName || selected.title || 'Testimonial'),
                                el('div', { style: { fontSize: '12px', color: '#555', fontStyle: 'italic' } },
                                    '"' + (selected.quote || '').substring(0, 80) + (selected.quote && selected.quote.length > 80 ? '…' : '') + '"'
                                )
                            ),
                            el('div', { style: { display: 'flex', gap: '8px' } },
                                selected.editUrl && el('a', {
                                    href: selected.editUrl,
                                    target: '_blank',
                                    rel: 'noopener noreferrer',
                                    className: 'components-button is-secondary is-small'
                                }, 'Edit Testimonial ↗'),
                                el(Button, {
                                    isSmall: true, isSecondary: true,
                                    onClick: clearSelection
                                }, 'Change Selection')
                            )
                        )
                        : el('div', {},
                            // Filters
                            el(SelectControl, { label: 'Person', value: filterPerson, options: personOptions, onChange: setFilterPerson }),
                            el(SelectControl, { label: 'Client', value: filterClient, options: clientOptions, onChange: setFilterClient }),
                            el(SelectControl, { label: 'Service', value: filterService, options: serviceOptions, onChange: setFilterService }),
                            el(SelectControl, { label: 'Industry', value: filterIndustry, options: industryOptions, onChange: setFilterIndustry }),
                            el(SelectControl, { label: 'Region', value: filterRegion, options: regionOptions, onChange: setFilterRegion }),
                            el(SelectControl, { label: 'Keyword', value: filterKeyword, options: keywordOptions, onChange: setFilterKeyword }),
                            el(TextControl, {
                                label: 'Search',
                                value: search,
                                onChange: setSearch,
                                placeholder: 'Search by name, quote…'
                            }),
                            loading && el('div', { style: { textAlign: 'center', padding: '8px', color: '#999', fontSize: '12px' } }, 'Searching…'),
                            !loading && results.length === 0 && el('div', { style: { textAlign: 'center', padding: '12px', color: '#999', fontSize: '12px' } },
                                search || filterPerson || filterClient || filterService || filterIndustry || filterRegion || filterKeyword
                                    ? 'No testimonials match your filters.'
                                    : 'No testimonials yet.'
                            ),
                            !loading && results.length > 0 && el('div', {
                                style: { maxHeight: '300px', overflowY: 'auto', border: '1px solid #e0e0e0', borderRadius: '4px' }
                            },
                                results.map(function(item) {
                                    return el('div', {
                                        key: item.id,
                                        style: { borderBottom: '1px solid #f0f0f0', padding: '8px 10px' }
                                    },
                                        el('div', { style: { display: 'flex', alignItems: 'center', gap: '6px', marginBottom: '3px' } },
                                            item.photoUrl
                                                ? el('img', { src: item.photoUrl, alt: '', style: { width: '24px', height: '24px', borderRadius: '50%', objectFit: 'cover', flexShrink: 0 } })
                                                : el('div', { style: { width: '24px', height: '24px', borderRadius: '50%', background: '#e0e0e0', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: '11px', flexShrink: 0 } }, '👤'),
                                            el('strong', { style: { fontSize: '12px' } }, item.personName || item.title || '#' + item.id)
                                        ),
                                        el('div', { style: { fontSize: '11px', color: '#555', overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap', marginBottom: '4px' } },
                                            '"' + (item.quote || '').substring(0, 80) + '"'
                                        ),
                                        el('div', { style: { display: 'flex', gap: '4px', flexWrap: 'wrap', alignItems: 'center' } },
                                            item.service && el('span', { style: { background: '#eef3e6', padding: '1px 4px', borderRadius: '3px', fontSize: '9px', color: '#3d6013' } }, item.service),
                                            item.industry && el('span', { style: { background: '#eef3e6', padding: '1px 4px', borderRadius: '3px', fontSize: '9px', color: '#3d6013' } }, item.industry),
                                            el(Button, {
                                                isSmall: true, isPrimary: true,
                                                onClick: function() { selectTestimonial(item); },
                                                style: { marginLeft: 'auto', fontSize: '11px', padding: '2px 8px' }
                                            }, 'Select')
                                        )
                                    );
                                })
                            )
                        )
                ),

                // Layout-specific styling panels
                attr.layout === 'full-width' && el(PanelBody, { title: 'Full-Width Styling', initialOpen: true },
                    el(SelectControl, {
                        label: 'Background style',
                        value: attr.bgStyle || 'white',
                        options: [
                            { label: 'White', value: 'white' },
                            { label: 'Light Grey', value: 'light' }
                        ],
                        onChange: function(val) { props.setAttributes({ bgStyle: val }); }
                    }),
                    el(TextControl, {
                        label: 'Case Study URL (optional)',
                        value: attr.caseStudyUrl || '',
                        onChange: function(val) { props.setAttributes({ caseStudyUrl: val }); }
                    }),
                    attr.caseStudyUrl && el(TextControl, {
                        label: 'Case Study Link Text',
                        value: attr.caseStudyText || 'Read Case Study',
                        onChange: function(val) { props.setAttributes({ caseStudyText: val }); }
                    })
                )
            );

            // ── Canvas: render exactly as it will appear on the frontend ─
            // Resolve display values: linked mode uses selected data, manual mode uses attributes
            var displayQuote = '';
            var displayCite = '';
            var displayTitle = '';
            var displayPhoto = '';
            var displayCaseStudyUrl = attr.caseStudyUrl || '';
            var displayCaseStudyText = attr.caseStudyText || 'Read Case Study';

            if (attr.mode === 'linked' && selected) {
                displayQuote = selected.quote || '';
                displayCite  = selected.personName || '';
                displayTitle = selected.personTitle || '';
                displayPhoto = selected.photoUrl || '';
            } else if (attr.mode === 'manual') {
                displayQuote = attr.quote || '';
                displayCite  = attr.cite || '';
                displayTitle = '';
                displayPhoto = attr.photoUrl || '';
            }

            // Empty state placeholder
            if (!displayQuote && !displayCite) {
                return [
                    inspector,
                    el('div', {
                        className: 'mini-testimonial',
                        style: { padding: '24px', textAlign: 'center', color: '#999', border: '1px dashed #ccc', borderRadius: '6px' }
                    },
                        el('span', { className: 'dashicons dashicons-editor-quote', style: { fontSize: '32px', display: 'block', marginBottom: '8px' } }),
                        el('p', { style: { margin: 0, fontSize: '13px' } },
                            attr.mode === 'linked'
                                ? 'Select a testimonial in Block Settings →'
                                : 'Enter testimonial content in Block Settings →'
                        )
                    )
                ];
            }

            // Canvas renderer dynamically matching layouts
            var previewBlock = null;
            if (attr.layout === 'full-width') {
                var bgMap = { white: '#fff', light: '#F4F6F8' };
                var bg = bgMap[attr.bgStyle] || '#fff';
                previewBlock = el('div', { className: 'full-width-testimonial full-width-testimonial--' + attr.bgStyle, style: { background: bg, borderLeft: '4px solid var(--color-primary-green,#215734)', padding: '2rem', display: 'flex', gap: '1.5rem', alignItems: 'center', maxWidth: '1200px', margin: '2rem auto', borderRadius: '2px' } },
                    displayPhoto && el('div', { className: 'full-width-testimonial__avatar', style: { width: '70px', height: '70px', borderRadius: '50%', overflow: 'hidden', border: '2px solid var(--color-primary-green,#215734)', flexShrink: 0 } },
                        el('img', { src: displayPhoto, style: { width: '100%', height: '100%', objectFit: 'cover' } })
                    ),
                    el('div', { className: 'full-width-testimonial__body', style: { flex: 1 } },
                        el('div', { className: 'full-width-testimonial__quote', style: { fontStyle: 'italic', fontSize: '1.1rem', lineHeight: '1.6', color: '#333', marginBottom: '0.75rem' } }, displayQuote),
                        el('div', { className: 'full-width-testimonial__byline', style: { fontSize: '0.9rem', fontWeight: '700', color: 'var(--color-primary-dark,#0e1b2b)' } }, '— ' + displayCite),
                        displayCaseStudyUrl && el('a', { href: displayCaseStudyUrl, className: 'full-width-testimonial__link', style: { color: 'var(--color-primary-dark,#0e1b2b)', fontWeight: '700', fontSize: '0.9rem', textDecoration: 'underline', display: 'inline-block', marginTop: '0.5rem' } }, displayCaseStudyText)
                    )
                );
            } else if (attr.layout === 'picker') {
                previewBlock = el('div', { className: 'testimonial-picker' },
                    el('blockquote', { className: 'testimonial-picker__quote' }, displayQuote),
                    el('div', { className: 'testimonial-picker__footer' },
                        displayPhoto && el('img', { src: displayPhoto, alt: '', className: 'testimonial-picker__photo' }),
                        displayCite && el('div', { className: 'testimonial-picker__person' },
                            el('span', { className: 'testimonial-picker__name' }, displayCite),
                            displayTitle && el('span', { className: 'testimonial-picker__title' }, displayTitle)
                        )
                    )
                );
            } else {
                previewBlock = el('div', { className: 'mini-testimonial' },
                    el('blockquote', null, displayQuote),
                    el('div', { className: 'mini-testimonial__footer' },
                        displayPhoto && el('img', { src: displayPhoto, alt: '', className: 'mini-testimonial__photo' }),
                        displayCite && el('cite', null, displayCite)
                    )
                );
            }

            return [
                inspector,
                previewBlock
            ];
        },
        save: function() {
            // Dynamic block — rendered server-side by PHP
            return null;
        }
    });


    // 10. Mini Case Study Callout
    blocks.registerBlockType('e3es/mini-case-study', {
        title: 'E3 Client Card',
        icon: 'id',
        category: 'layout',
        attributes: {
            title: { type: 'string', default: '' },
            text: { type: 'string', default: '' },
            imageUrl: { type: 'string', default: '' },
            imageAlt: { type: 'string', default: '' },
            linkText: { type: 'string', default: 'View Case Study' },
            linkUrl: { type: 'string', default: '' }
        },
        edit: function(props) {
            var attr = props.attributes;
            
            var inspector = el(InspectorControls, {},
                el(PanelBody, { title: 'Case Study Settings', initialOpen: true },
                    el('div', { style: { marginBottom: '15px' } },
                        el('label', { style: { display: 'block', marginBottom: '5px', fontWeight: 'bold' } }, 'Image'),
                        el(MediaSelect, {
                            url: attr.imageUrl,
                            onSelect: function(media) {
                                props.setAttributes({ imageUrl: media.url, imageAlt: media.alt || '' });
                            }
                        })
                    ),
                    el(TextControl, {
                        label: 'Title',
                        value: attr.title,
                        onChange: function(val) { props.setAttributes({ title: val }); }
                    }),
                    el(TextareaControl, {
                        label: 'Text Description',
                        value: attr.text,
                        onChange: function(val) { props.setAttributes({ text: val }); }
                    }),
                    el(TextControl, {
                        label: 'Link Text',
                        value: attr.linkText,
                        onChange: function(val) { props.setAttributes({ linkText: val }); }
                    }),
                    el(TextControl, {
                        label: 'Link URL',
                        value: attr.linkUrl,
                        onChange: function(val) { props.setAttributes({ linkUrl: val }); }
                    })
                )
            );

            return [
                inspector,
                el('div', { className: 'mini-case-study' },
                    attr.imageUrl ? el('img', { src: attr.imageUrl, alt: attr.imageAlt, className: 'mini-case-study__img' }) : el('div', { style: { width: '120px', height: '90px', background: '#eee', display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0 } }, 'No Image'),
                    el('div', { className: 'mini-case-study__content' },
                        el(RichText, {
                            tagName: 'h4',
                            value: attr.title,
                            onChange: function(val) { props.setAttributes({ title: val }); },
                            placeholder: 'Title'
                        }),
                        el(RichText, {
                            tagName: 'p',
                            value: attr.text,
                            onChange: function(val) { props.setAttributes({ text: val }); },
                            placeholder: 'Description'
                        }),
                        el(RichText, {
                            tagName: 'span',
                            className: 'mini-case-study__link',
                            value: attr.linkText,
                            onChange: function(val) { props.setAttributes({ linkText: val }); },
                            placeholder: 'Link Text'
                        })
                    )
                )
            ];
        },
        save: function(props) {
            var attr = props.attributes;
            return el('div', { className: 'mini-case-study' },
                attr.imageUrl && el('img', { src: attr.imageUrl, alt: attr.imageAlt, className: 'mini-case-study__img' }),
                el('div', { className: 'mini-case-study__content' },
                    el(RichText.Content, { tagName: 'h4', value: attr.title }),
                    el(RichText.Content, { tagName: 'p', value: attr.text }),
                    el(RichText.Content, { tagName: 'a', href: attr.linkUrl || '#', className: 'mini-case-study__link', value: attr.linkText })
                )
            );
        }
    });

    // 11. Texas Interactive Map Section
    var MAP_REGIONS = [
        { key: 'panhandle', attr: 'panhandle', label: 'Far West Texas' },
        { key: 'west', attr: 'west', label: 'West Texas' },
        { key: 'north', attr: 'north', label: 'North Texas' },
        { key: 'northeast', attr: 'northeast', label: 'North East Texas' },
        { key: 'southeast', attr: 'southeast', label: 'South East Texas' },
        { key: 'central', attr: 'central', label: 'Central Texas' },
        { key: 'hill-country', attr: 'hillCountry', label: 'Hill Country' },
        { key: 'south', attr: 'south', label: 'South Texas' }
    ];

    var REGION_DEFAULTS = {
        panhandle: { 
            headline: 'Far West Texas', 
            text: 'From El Paso to the Permian Basin, E3 delivers energy solutions to school districts and municipalities in the far western reaches of Texas.',
            photo: 'https://www.e3es.com/next/images/clients/Prosper-ISD-after-0217-Edit-768x575.jpg'
        },
        west: { 
            headline: 'West Texas', 
            text: 'Serving the wide-open spaces of West Texas with innovative HVAC, lighting, and water treatment solutions for rural communities.',
            photo: 'https://www.e3es.com/next/images/clients/Sanger-Exterior-Resized-768x530.jpg'
        },
        north: { 
            headline: 'North Texas', 
            text: 'E3 partners with school districts and cities across the DFW Metroplex and North Texas to modernize aging infrastructure.',
            photo: 'https://www.e3es.com/next/images/clients/55182270675_296ab7a759_k-768x512.jpg'
        },
        northeast: { 
            headline: 'North East Texas', 
            text: 'From Tyler to Texarkana, E3 brings turnkey design+build solutions to communities in the Piney Woods region.',
            photo: 'https://www.e3es.com/next/images/clients/51585631196_8cbb6f338f_h-768x512.jpg'
        },
        southeast: { 
            headline: 'South East Texas', 
            text: 'Serving the Houston metro and Gulf Coast with comprehensive energy and water infrastructure upgrades.',
            photo: 'https://www.e3es.com/next/images/clients/KOUNTZE-768x514.jpeg'
        },
        central: { 
            headline: 'Central Texas', 
            text: 'The E3 home base, delivering transformative projects to Austin, Waco, Temple, and the surrounding communities.',
            photo: 'https://www.e3es.com/next/images/clients/51496446012_7397fcb563_k-768x512.jpg'
        },
        hillCountry: { 
            headline: 'Hill Country', 
            text: 'From San Antonio to Fredericksburg, E3 provides tailored energy solutions for the Texas Hill Country.',
            photo: 'https://www.e3es.com/next/images/clients/Needville-ISD-photo-768x557.jpg'
        },
        south: { 
            headline: 'South Texas', 
            text: 'Partnering with Rio Grande Valley communities to upgrade facilities and reduce energy costs across South Texas.',
            photo: 'https://www.e3es.com/next/images/clients/Carrizo-Springs-8-768x576.jpg'
        }
    };

    var mapAttributes = {
        defaultPhoto: { type: 'string', default: '' },
        defaultHeadline: { type: 'string', default: 'Statewide Impact' },
        defaultText: { type: 'string', default: 'E3 Entegral Solutions has successfully partnered with public entities across the entire state of Texas. From K-12 school districts to municipal governments and healthcare facilities, our dedicated teams deliver tailored, localized support and transformative energy solutions that benefit communities from the Panhandle to the Gulf Coast.' }
    };

    MAP_REGIONS.forEach(function(r) {
        var d = REGION_DEFAULTS[r.attr] || { headline: r.label, text: '', photo: '' };
        mapAttributes[r.attr + 'Photo'] = { type: 'string', default: d.photo || '' };
        mapAttributes[r.attr + 'Headline'] = { type: 'string', default: d.headline };
        mapAttributes[r.attr + 'Text'] = { type: 'string', default: d.text };
        mapAttributes[r.attr + 'LinkPageId'] = { type: 'number', default: 0 };
    });

    blocks.registerBlockType('e3es/texas-interactive-map', {
        title: 'E3 Texas Interactive Map',
        icon: 'location',
        category: 'layout',
        attributes: mapAttributes,
        edit: function(props) {
            var attr = props.attributes;

            // Fetch published pages for link picker
            var pagesRaw = wp.data.useSelect(function(select) {
                return select('core').getEntityRecords('postType', 'page', {
                    per_page: 100, status: 'publish', orderby: 'title', order: 'asc'
                });
            });
            var pageOptions = [{ value: 0, label: '- Select Page -' }];
            (pagesRaw || []).forEach(function(p) {
                pageOptions.push({ value: p.id, label: p.title.rendered });
            });

            // Build InspectorControls
            var inspectorArgs = [InspectorControls, {},
                el(PanelBody, { title: 'Default Content', initialOpen: true },
                    el('div', { style: { marginBottom: '12px' } },
                        el('label', { style: { fontWeight: '600', display: 'block', marginBottom: '5px' } }, 'Default Photo'),
                        el(MediaSelect, {
                            url: attr.defaultPhoto,
                            onSelect: function(media) { props.setAttributes({ defaultPhoto: media.url }); }
                        }),
                        attr.defaultPhoto && el(Button, {
                            isDestructive: true, isSmall: true, style: { marginTop: '4px' },
                            onClick: function() { props.setAttributes({ defaultPhoto: '' }); }
                        }, 'Remove Photo')
                    ),
                    el(TextControl, {
                        label: 'Default Headline',
                        value: attr.defaultHeadline,
                        onChange: function(val) { props.setAttributes({ defaultHeadline: val }); }
                    }),
                    el(TextareaControl, {
                        label: 'Default Text',
                        value: attr.defaultText,
                        onChange: function(val) { props.setAttributes({ defaultText: val }); }
                    })
                )
            ];

            MAP_REGIONS.forEach(function(r) {
                inspectorArgs.push(
                    el(PanelBody, { title: r.label + ' Region', initialOpen: false },
                        el('div', { style: { marginBottom: '12px' } },
                            el('label', { style: { fontWeight: '600', display: 'block', marginBottom: '5px' } }, 'Photo'),
                            el(MediaSelect, {
                                url: attr[r.attr + 'Photo'],
                                onSelect: function(media) {
                                    var u = {}; u[r.attr + 'Photo'] = media.url;
                                    props.setAttributes(u);
                                }
                            }),
                            attr[r.attr + 'Photo'] && el(Button, {
                                isDestructive: true, isSmall: true, style: { marginTop: '4px' },
                                onClick: function() { var u = {}; u[r.attr + 'Photo'] = ''; props.setAttributes(u); }
                            }, 'Remove Photo')
                        ),
                        el(TextControl, {
                            label: 'Headline',
                            value: attr[r.attr + 'Headline'],
                            onChange: function(val) { var u = {}; u[r.attr + 'Headline'] = val; props.setAttributes(u); }
                        }),
                        el(TextareaControl, {
                            label: 'Text',
                            value: attr[r.attr + 'Text'],
                            onChange: function(val) { var u = {}; u[r.attr + 'Text'] = val; props.setAttributes(u); }
                        }),
                        el(SelectControl, {
                            label: 'Link Page',
                            value: attr[r.attr + 'LinkPageId'],
                            options: pageOptions,
                            onChange: function(val) { var u = {}; u[r.attr + 'LinkPageId'] = parseInt(val, 10); props.setAttributes(u); }
                        })
                    )
                );
            });

            var inspector = el.apply(null, inspectorArgs);

            var clsStyle = el('style', null, 
                '.texas-svg-map { width: 100%; max-width: 650px; height: auto; filter: drop-shadow(0 10px 20px rgba(0,0,0,0.15)); pointer-events: none; }\n' +
                '.texas-region { cursor: pointer; pointer-events: all; }\n' +
                '.texas-region path, .texas-region polygon, .texas-region rect { transition: all 0.3s ease; }\n' +
                '.texas-svg-map.has-active .texas-region:not(.active) path, .texas-svg-map.has-active .texas-region:not(.active) polygon, .texas-svg-map.has-active .texas-region:not(.active) rect { fill: #d3dbd5 !important; transition: fill 0.3s ease; }\n' +
                '.texas-region:hover, .texas-region.active { filter: brightness(1.2) drop-shadow(0 8px 16px rgba(0,0,0,0.5)); stroke: #ffffff; stroke-width: 4px; }\n' +
                '.cls-1 { fill: #2b5434; }\n' +
                '.cls-2 { fill: #598e64; }\n' +
                '.cls-3 { fill: #31623d; }\n' +
                '.cls-4 { fill: #034411; }\n' +
                '.cls-5 { fill: #5b8764; }\n' +
                '.cls-6 { fill: #65b776; }\n' +
                '.cls-7 { fill: #54725a; }\n' +
                '.cls-8 { fill: #115620; }\n' +
                '.cls-9 { fill: none; }'
            );

            var svgMap = el('svg', { id: 'texas-map-svg', viewBox: '0 0 941.76 907.17', className: 'texas-svg-map', xmlns: 'http://www.w3.org/2000/svg' },
                el('defs', null, clsStyle),
                el('g', { id: 'Layer_1-2', 'data-name': 'Layer 1' },
                    el('g', { className: 'texas-region', 'data-region': 'panhandle' },
                        el('path', { className: 'cls-1', d: 'M625.37,271.22c-3.01-.1-4.59-.27-6.39-3.06-6.58-9.19-16.48,14.39-19.8,2.98.36-12.2-8.33-1.94-8.68-9.28-.05-4.19-1.51-9.2-6.52-5.59-3.56.59-9.11-1.64-12.88-1.13-2.48-.02-1.4,6.05-4.73,4.49-6.32-7.95-15.5-1.81-23.12-6.03-3.75-3.62-10.11-1.56-14.61-3.1-2.33-1.43-.54-4.57-2.19-6.73-2.42-2.19-3.04-7.27-6.69-7.09-4.38-1.98-5.68-3.6-8.22,1.67-2.51.26-2.98-2.32-6.84-.25-10.36,5.8-11.52-9.11-19.29-11.6-1.46-1.04-4.6,0-5.36-1.51-1.15-34.93,1.6-104.83-1.86-133.08-55.21.18-122.84-1.67-173.01-2.19-1.76,103.62-6.17,210.23-9.82,313.06-27.93-.6-84.62-3.23-108.69-3.72-1.97,27.93-1.31,53.4-3.71,83.08-.94,7.07,1.81,8.26-6.01,10.84-4.13,6.35,5.25.95,6.4,18.25,1.89,5.53,5.99,9.43,8.57,14.47-.31,16.55-4.5,15.32,6.36,30.43.88,10.24,10.27,16.23,18.87,20.16,5.92,9.06,14.63,16.01,25.45,18.46,4.36,4.92,10.16,9.4,16.94,9.86,6.97,7.77,25.03,19.67,31.44,5.88,2.02-1.99,3.99-5.7,6.61-7.36,1.98-1,4.46-1.67,5.74-3.68,1.35-2.35-.84-5.87,1.16-8.11,3.61-4.48,3.61-10.47,6.72-15.26,1.02-4.57,4.97-7.46,6.35-11.46,4.79-2.35,10.8-1.55,15.86-2.66,3.34-1.38,3.24-8.25,8.02-4.82,9.84,5.84,10.08,2.93,19.54,5.2,3.29-.09,11.61.56,11.61.56,0,0,.22-29.67,1.17-33.49-.73-5.57,3.61-4.21,5.18-7.68-3.27-7.86,7.46-18.36-1.92-24.22-1.19-.7-3.29.1-3.66-1.63-2.85-13.26.88-20.67-11.7-20.97-2.88-.07-20.83-4.72-20.83-4.72l66.15,1.83s-.1-49.4.39-67.86c34.3-.18,102.16-2.52,134.48-.3,2.9,4.56,12.03,20.41,14.34,24.02,2.55,2.23,6.92-.89,10.06,0,2.14.45,2.61,2.32,4.1,1.84,4.84-3.34,15.89-8.44,19.17-12.65-11.29-23.53-5.92,5.32-22.26-27.27-.57-1.71,1.94-2.31,2.6-4.28.67-4.47.02-10.01.06-14.42-.17-2.93-4-1.41-5.52-2.68-.96-5.37-.47-20.63-.68-25.25.81-3.39,7.13.29,8.44-3.22.39-1.12.68-2.24,2.09-2.13,5.28-.02,20.58.06,26.87-.04.85-.06.81-.77.82-1.45.15-4.35-.61-21.34-.12-28.22,6.3-1.31,17.85-.4,24.51-1.2,1.75-4.97-.61-28.35-.03-34.32.1-.71-.37-1.25-.94-1.39Z' })
                    ),
                    el('g', { className: 'texas-region', 'data-region': 'north' },
                        el('path', { className: 'cls-2', d: 'M715.7,340.75c.36-4.12,1.69-5.37,5.57-5.84,1.48-6.23-.62-23.26-.3-32.15-.08-4.83-.6-20.36-.71-25.83-.19-.88.44-1.53.93-2.02.42-.5-1.48-2.05-2.34-2.56-3.61-3.41-7.48.07-11.45.93-2.91.23-6.38.94-7,4.18-.35,4.14-4.79,1.38-6.9,3.58-1.35,1.52-2.62,4.39-4.28,1.37-2.65-4.05-8.26-4.21-12.26-7.03-1.19-.77-.43-2.2-.76-3.28-.35-1.12-3.93-1.63-5.18-.86-1.54,1.03-1.65,4.85-4.26,3.87-4.66-3.08-5.59.66-8.74-4.9-1.3-2.17-4.08-4.23-5.05-.8-.78,7.99-5.71,3.47-5.98,10.3-.51,3.84-1.69,4.56-4.04.73-.97-2.78.24-6.15-2.12-8.64-1.38-1.95-2.89.78-4.37,1.39-5.14.03-3.57,1.94-7.2,3.64-3.07.59-2.74-4.3-4.53-5.68-3.29-4.21-.01,31.64-.89,32.94-4.75,1.59-14.4.41-19.96,1.01-2.16.37-5.44-.84-5.23,1.43.02,4.36.31,24.01.27,27.87-.44,1.76-3.29.82-4.72,1.04-7.37.39-18.56-.78-24.44.55-.85,6.49-9.09.95-9.18,4.97.06,5.04.25,25.19.33,29.5.4,2.79,6.1-.27,6.56,2.86.12,3.23.41,8.39,0,11.7-.36,2.24-4.94,2.61-3.5,5.11,2.29,3.81,10.39,17.25,12.58,20.86,1.14,2.01,2.94-.7,4.94-.33,1.83.12,2.32,3.51,4.23,2.51,7.58-4.22,20.47-12.87,28.81-18.41,5.53-4.12,4.21.37,8.82,1.79,2.51.79,3.45-1.93,5.27-2.82,5.23-1.76,14.99-4.82,21.11-5.91,2.25,1,8.57,16.85,10.76,13.78,5.57-3.47,23.67-15,30.18-18.8,2.24-1.08,10.23-2.89,18.79-3.7,3.34-.31-.53-29.18.22-32.59.69-1.72,4.3-.7,6.05-1.19,5.1-.98-.59-2.28-.03-4.56Z' })
                    ),
                    el('g', { className: 'texas-region', 'data-region': 'central' },
                        el('path', { className: 'cls-3', d: 'M575.28,479.41c-2.46-3.6.38-12.63-5.57-12.29.67-6.15-3.17-3.02-4.04-11.94-1.13-3.45-4.09-4.67-6.79-5.52-1.36-7.24-3.16-.15-15.86-5.89-2.51-1.35.68-2.94,1.79-3.98,2.93-2.33,11.53-9.07,14.14-11.15,1.97-1.18.24-2.41-.48-3.94-3.57-5.98-12.26-20.64-14.07-23.62.39-2.01-16.92-.35-19.02-.83-38.39,1.28-84.34-2.33-121.68.2-.26,15.98-1.57,69.14-1.57,69.14,0,0-63.94-.62-69.97-1.48-2.05-.82,9.92,4.29,12.94,6.06,4.5,2.43,10.53.8,14.71,3.81,9.41,5.29,4.36,21.47,16.09,23.54,3.01,2.32,1.14,7.24-1.25,9.24-5.01,2.98,1.04,7.64.08,9.37-2.01,1.64-4.75,3.91-4.26,6.82.09,7.57-.04,35.79-.04,35.79,0,0,9.87.53,12.21.68s8.67,1.12,11.69,5.77c1.07,1.65,24.36,23.13,30.23,27.38,9.21,6.17,8.1,2.37,10.55-5.77,2.47-4.74.24-12.11,1.79-17.36,59.39-2.71,33.63,11.21,43.45-28.74.64-2.82,2.45-3.46,4.96-3.03,14.01,1.72,6.67-5.56,9.09-14.73,5.17-1.16,21.01.45,26.7-.46,2.19-19.29-6.57-13.59,19.23-14.68,2.94.13.98-3.89,1.46-7.8-.03-5.38-.18-13.45-.22-18.52.15-1.57-.55-2.76,1.05-2.8,4.21-.17,27.12-.92,32.11-1.14,1.31-.03,1.01-1.42.56-2.16Z' })
                    ),
                    el('g', { className: 'texas-region', 'data-region': 'south' },
                        el('path', { className: 'cls-4', d: 'M647.17,675.13c-3.84-3.51-8.6-1.3-13.52-5.17-.64-3.21,11.36-13.54,5.16-14.76-5.92-2.82-7.06-2.7-9.95-8.46-1.53-.9-5.35-.21-6.96-1.65-3.36-5.91-4.28-8.89-11.58-10.33-4.08-2.99-5.49.53-9.22-.48-3.11-1.92-4.74-5.01-7.34-7.59-.85-1.29-2.97-2.62-2.89-4.17,3.56-3.62,17.42-15.05,24.68-21.5,1.06-1.04,3.06-2.03,1.92-3.11-13.11-13.04.39-10.79-20.74-10.16-5.74.7-1.62-5.45-4.56-7.71-5.22-4.16-14.85-7.17-12.95-15.46-1.08-3.99-6.74-1.39-9.72-2.33-7.95-2.26-19.97,5.75-24.96-3.67-.66-2.61.93-23.35-1.4-23.41-4.08-.15-16.36.36-20.47.29-3.27-.17-1.08-6.99-1.85-9.16-.13-1.1-.5-1.32-1.46-1.36-4.98.27-21.82-.67-27.1.05-2.73,12.22,5.29,16.59-11.8,14.57-3.62,1.78-2.77,7.37-3.81,11.13-2.27,4.63-.43,9.82-2.35,14.53-.62,2.91,3.42,5.87-2.28,5.9-6.86-.38-29.14.08-36.3-.09-1.67-.03-1.57.57-1.66,2.69-.56,5.94.63,13.24-2.06,18.24-6.37,10.99-2.59,9.64,3.17,18.2,1.12,3.04,1.07,8.07,2.74,11.21,4.04,5.2,6.61,11.03,9.32,16.96,6.01,9.96,12.05,21.58,16.4,32.43,4.99,6.46,13.47,12.45,17.27,18.73,4.23,4.66,9.63,12.73,13.18,17.95,2.26,3.72,8.22,2.64,10.77,6.03,1.39,1.71.81,4.65,2.3,6.39,3.72,2.48.5,9.53-.61,12.14,2.23,6.04,7.54,4.47,5.03,13.64.06,3.95,2.2,8.53,4.43,11.78,2.68,5.88,11.54,21.04,13.65,29.09.97,1.58,2.82,2.79,3.04,4.74-.03,3.69,1.72,6.21,5.82,5.73,4.98.14,6.94,2.77,11.64,2.43,4.65.87,7.15,5.8,11.36,7.79,10.91,1.04,20.21,6.74,29.29,12.48,2.32,1.37,5.13.21,7.31.83,1.53.65,3.17.84,4.73.09,6.18-1.97,12.22.96,18.55,1.44,3.63-.43,5.5,2.54,7.56,4.96,2.96,2.34,6.65,4.61,10.04,6.56,9.62,3.46,4.32-4.05,10.09-5.34,2.11-.72,4.6-1.95,6.71-2.01.97-.09,2.17.13,2.78-.8,1.46-4.71.23-9.71-.57-14.45-2.49-10.35-4.7-20.92-8.11-30.49-1.25-12.11-3.94-36.61-4.87-45.59-.45-9.16,6.36-17.22,7.64-26.17,5.29-12.59,10.33-20.51,19.82-30.46,2.22-1.81-16.28-2.66-25.31-3.08Z' })
                    ),
                    el('g', { className: 'texas-region', 'data-region': 'west' },
                        el('path', { className: 'cls-5', d: 'M188.46,399.11c-26.63-2.3-76.43-6.08-101.72-6.87,8.76,16.64,30.03,43.31,42.69,59.33,1.33,2.31,2.06,5.46,4.72,6.55,8.11,2.95,12.22,11.85,15.64,19.25,5.72,7.78,10.3,6.35,17.52,10.85,1.13,1.09,1.05,2.5,2.72,3.27,3.04,1.39,5.39,4.39,8.43,6.01,2.24-.24,7.81-1.26,7.91-4.6,2.35-26.69,2.65-67.01,4.22-93.74,0,0-1.35,0-2.13-.05Z' })
                    ),
                    el('g', { className: 'texas-region', 'data-region': 'hill-country' },
                        el('path', { className: 'cls-6', d: 'M672.48,678.22c13.46-9.72,28.22-22.41,41.81-32.02,10.15-6.35,22.56-10.98,32.23-18.06,1.68-1.69,14.33-7.97,5.07-7.47-22.72-13.6-6.74-7.08-17.69-18.17-1.13-3.88-.64-9.52-3.97-12.4-21.04-24.33.09-18.88-13.11-35.67-1.66-1.15-1.12-3.13-1.56-4.87-.92-2.86,1.16-5.4.25-8.49-.45-3.71-5.51-5.65-4.33-9.5.36-2.47,2.4-3.8,3.63-5.69,2.72-5.75-4.93-6.5-1.26-10.09,2.01-2.17,1.43-4.46-.63-6.23-3.33-6.42-2.97-13.01-3.37-20.23,1.21-7.68-5.92-13.11-5.26-20.51-2.89-6.36-10.24-21.38-.26-24.47,4.48-3.03,21.76-11.36,26.07-15.63.6-4.69-2-4.55-4.66-7.07-1.45-3.45-6.14-3.72-7.33-7.27-.43-3.09-11.6-27.06-14.35-27.57-3.94-1.98-7.56-8.14-12.72-6.39-7.76,4.01-23.92,16.72-31.24,18.24-3.72-2.32-5.95-11.46-10.08-13.29-6.87,1.37-17.13,4.25-23.43,6.63-1.98,2.95-4.16.87-6.28-.66-1.44-.97-2.12-.81-3.79.22-9.86,6.32-21.29,12.68-30.92,18.6-3.84,1.99,2.92,6.2-1.39,7.97-3.45,2.12-9.84,6.26-13.26,8.12-1.57.82-2.43-1.24-3.81-1.26-7.19,3.36-22.72,12.62-28.37,15.52-.83.43-1.68.97-1.74,1.96.02,3.79,4.01,3.65,6.81,4.11,3.1,1.01,5.91,3.71,9.19,2.82.44-.09.93-.09,1.1.36,1.21,5.87,3.71-.65,7.35,8.87,1.16,1.94,2.24,3.98,3.04,6.04.16,6.64,4.18,2.63,5,8.08.05,1.93,2.64,8.31-.49,7.89-37.61,2.53-28.68-10.1-29.35,28.7-.42,2.85-22.53-1.53-20.54,2.93,2,30.29-7.7,24.81,21.44,24.61,1.08-.08,1.93.15,1.89,1.36.12,3.37.31,17.01.42,21.23-.04,3.57,7.2,4.59,9.95,6.22,5.7.68,11.39-3.54,17.12-1.09,1.65.72,3.66.44,5.17.58,1.98,8.38,9.51,12.7,15.7,17.56.74,2.1-.04,6.67,2.93,7.51,14.07.29,10.85-5.19,20.74,7.03.66.89.99,1.24.37,1.94-3.22,2.88-22.52,19.45-26.17,22.66-1.02.7-.28,1.6.38,2.32,18.43,22.01,6.86,4.99,26.07,15.38,2,1.75,2.19,5.26,3.91,6.96,2.75,2.24,8.45.82,9.53,5.51,1.26,3.74,10.09,1.9,8.92,6.63-.7,2.77-9.16,13.12-6.96,14.55,2.63-.09,4.53,1.56,6.65,2.71,3.92.03,7.06.99,10.59,2.63,6.62.15,17.87,0,25-.32ZM577.95,564.78h0s.03-.04.03-.04l.02.02-.04.02h0ZM719.36,577.92h.08v-.17h.27l-.35.36v-.18h0ZM714.27,553.28l-.07.02-.02-.02h.1ZM709,500.18h.12l-.1.1-.02-.1h0ZM700.44,656.41l-.16-.66h.16v.66h0Z' })
                    ),
                    el('g', { className: 'texas-region', 'data-region': 'southeast' },
                        el('path', { className: 'cls-7', d: 'M867.07,461.62c-.95-2.6-1.22-6.24-2.56-8.76-2.65-1.95-3.51-5.24-5.69-7.88-2.35-6.24-3.33-14.65-10.32-17.95-7.47-8.02-20.65-4.73-31.29-5.5-9.1.86-3.53-14.17-12.97-13.24-18.92,1.96-37.87-5.22-24.74,22.06,1.62,2.68.6,5.49-2.43,6.62-5.57,1.62-6.98-6.92-11.43-8.18-10.08-1.8-21.42,1.52-31.33,4.72-2.85.36-.07-5.92-3.61-6.93-8.81,2.9-25.99,14.79-33.39,18.4-7.06,7.2,5.14,29.6,7.35,38.51,2.51,8.35-.21,17.76,4.38,25.73,2.59,6.5-2.34,5.75,2.13,13.86,0,3.67-4.32,6.72-3.76,10.14,1.53,4.71,5.21,8.55,3.57,13.95-1.15,7.4,7.8,9.61,5.22,16.63.06,1.71.77,3.45-.23,5.04-1.13,1.92-.15,3.78-.06,6.25-.46,3.45,1.67,5.88,4.25,7.9,2.26,2.36,3.49,5.41,5.88,7.62,3.28,3.28,2.57,8.31,4.34,12.35.74,1.76,2.05,3.11,2.76,4.53,1.03,1.87,1,4.35,2.77,5.76,11.1,2.99,10.16,12.85,17.29,9.77,11.7-3.13,18-14.61,27.23-21.54,5.9-4.12,13.81-9.49,19.93-13.84,3.82-2.78-.78-5.11,3.19-7.64,13.79-10.38,28.55-17.36,45.98-20.03,1.86-.14,4.1.13,3.81-2.16-.3-3.17-3.69-4.69-5.02-7.19-2.56-10.79,12.74-15.25,11.89-25.78-.48-3.85-.63-8.08-3.14-11.25-2.73-4.82,3.4-6.5.74-15.31,1.1-7.95,7.51-14.57,8.11-22.83.89-4.78-.16-9.24,1.16-13.83ZM808.34,410.84v.17l-.16-.16h.16ZM702.22,474.17c-.22-.03-1.55.68-.57-.2.16-.11.22-.32.3-.03.09.05.25.07.3.17l-.02.06Z' })
                    ),
                    el('g', { className: 'texas-region', 'data-region': 'northeast' },
                        el('path', { className: 'cls-8', d: 'M848.18,422.34c.89-12.68-9.57-17.6-15.25-26.33-3.7-36.34-2.49-70.91-6.57-107.61-6.78-6.62-18.34,4.41-24.48-3.92-4.17-2.02-8.95-3.94-13.61-4.88-2.88-1.94-5.54-3.83-9.19-3.64-3-.71-4.01-4.43-6.75-5.74-4.97-2.43-10.17-7.53-16.13-6.62-13.45,11.6-14.44,1.53-23.84,2.67-5.57,2.63-15.4,4.2-14.62,12.31.69,14.3.16,30.45.97,45.01-.29,2.87,1.29,6.88-.97,9.08-4.02,1.34-5.29,5.52-5.6,9.35-.72,3.07-5.34.93-4.85,5.33-.45,33.97,9.27,30.01-18.7,30.68-1.83.07-3.32.34-2.88,1.55.74,1.69,3.78,2.66,5.62,3.27,19.24,11.06,21.91,32.11,24.93,35.85s13.6,15.96,14.1,17.77c1.76,4.41,7.87-1.1,10.9-1.38,7.93-1.28,16.56-5.46,24.17-1.68,3.79,1.88,7.51,10.12,12.06,7.73,14.19-7.46-.43-19.42,2.17-25.66s19.6-3.2,24.2-4.14c8.26.8,1.61,14.68,11.76,13.37,5.88.31,15.17.85,21.64,1.2,4.42.16,10.05,3.08,10.91-3.57ZM806.42,289.91l.03-.12.08.68-.5-.17.39-.39h0ZM805.84,290.46l-.04.04h-.05s.09-.04.09-.04ZM735.58,267.46l-.14-.1.14-.55v.65h0Z' })
                    ),
                    el('rect', { className: 'cls-9', width: '941.76', height: '907.17' })
                )
            );

            var regionLinksList = el('div', { id: 'region-links-list', style: { marginTop: '2rem', marginBottom: '1rem', textAlign: 'center' } },
                el('ul', { style: { listStyle: 'none', padding: '0', display: 'flex', flexDirection: 'row', justifyContent: 'center', flexWrap: 'wrap', gap: '1rem' } },
                    MAP_REGIONS.map(function(r) {
                        return el('li', { key: r.key },
                            el('a', { href: '#', className: 'btn btn--outline region-link', 'data-region': r.key }, r.label)
                        );
                    })
                )
            );

            var mapLeft = el('div', { className: 'map-left' }, svgMap);
            var mapRight = el('div', { className: 'map-right' },
                el('div', { style: { minHeight: '400px', display: 'flex', flexDirection: 'column', justifyContent: 'flex-start' } },
                    el('h2', { className: 'map-content-title' }, attr.defaultHeadline || 'Statewide Impact'),
                    attr.defaultPhoto && el('img', { src: attr.defaultPhoto, alt: '', className: 'map-content-img', style: { maxWidth: '100%', marginBottom: '1rem', borderRadius: '8px' } }),
                    el('p', { className: 'map-content-text' }, attr.defaultText || '')
                )
            );

            return [
                inspector,
                el('section', { className: 'map-section' },
                    el('div', { className: 'map-container' },
                        mapLeft,
                        mapRight
                    ),
                    regionLinksList
                )
            ];
        },
        save: function() {
            return null;
        }
    });

    // Helper to calculate background styles for E3 Intro Banner
    function getBannerStyles(attr) {
        var rgbMap = {
            green: '33, 87, 52',
            sage: '125, 160, 68',
            black: '0, 0, 0',
            blue: '16, 44, 87'
        };
        var rgb = rgbMap[attr.bgOverlayColor || 'green'] || rgbMap.green;
        var opacity = typeof attr.bgOpacity === 'number' ? attr.bgOpacity : 0.85;
        
        var gradient = '';
        switch (attr.bgFadeType || 'flat') {
            case 'vertical':
                gradient = 'linear-gradient(to bottom, rgba(' + rgb + ',' + (opacity * 0.4) + '), rgba(' + rgb + ',' + opacity + '))';
                break;
            case 'horizontal':
                gradient = 'linear-gradient(to right, rgba(' + rgb + ',' + opacity + '), rgba(' + rgb + ',' + (opacity * 0.3) + '))';
                break;
            case 'vignette':
                gradient = 'radial-gradient(circle, rgba(' + rgb + ',' + (opacity * 0.4) + ') 0%, rgba(' + rgb + ',' + opacity + ') 100%)';
                break;
            case 'vignette-center':
                gradient = 'radial-gradient(circle, rgba(' + rgb + ',' + opacity + ') 0%, rgba(' + rgb + ',' + (opacity * 0.4) + ') 100%)';
                break;
            case 'flat':
            default:
                gradient = 'linear-gradient(rgba(' + rgb + ',' + opacity + '), rgba(' + rgb + ',' + opacity + '))';
                break;
        }

        var heroStyle = {};
        if (attr.bgImageUrl) {
            heroStyle.backgroundImage = gradient + ', url(' + attr.bgImageUrl + ')';
            heroStyle.backgroundSize = 'cover';
            var fx = attr.focalPointX !== undefined ? attr.focalPointX : 0.5;
            var fy = attr.focalPointY !== undefined ? attr.focalPointY : 0.5;
            heroStyle.backgroundPosition = (fx * 100) + '% ' + (fy * 100) + '%';
            heroStyle.backgroundRepeat = 'no-repeat';
        } else {
            heroStyle.backgroundColor = 'rgba(' + rgb + ', 1)';
        }

        return heroStyle;
    }

    // Helper to calculate text styles for E3 Intro Banner
    function getTitleStyles(attr) {
        var shadowMap = {
            none: 'none',
            subtle: '0 2px 4px rgba(0,0,0,0.3)',
            strong: '0 4px 15px rgba(0,0,0,0.8), 0 2px 4px rgba(0,0,0,0.5)'
        };
        var titleStyle = {
            marginBottom: '0',
            textAlign: attr.textAlignment || 'center',
            textTransform: attr.textCase || 'uppercase',
            textShadow: shadowMap[attr.textShadow || 'subtle'] || shadowMap.subtle
        };
        
        if (attr.textSkew) {
            titleStyle.transform = 'skewX(-5deg)';
            titleStyle.display = 'inline-block';
        }

        return titleStyle;
    }

    // 9. E3 Intro Banner (Page Hero)
    blocks.registerBlockType('e3es/intro-banner', {
        title: 'E3 Intro Banner',
        icon: 'welcome-widgets-menus',
        category: 'layout',
        attributes: {
            title: { type: 'string', default: '' },
            bgImageUrl: { type: 'string', default: '' },
            bgOpacity: { type: 'number', default: 0.85 },
            bgOverlayColor: { type: 'string', default: 'green' },
            bgFadeType: { type: 'string', default: 'flat' },
            textShadow: { type: 'string', default: 'subtle' },
            textAlignment: { type: 'string', default: 'center' },
            textCase: { type: 'string', default: 'uppercase' },
            textSkew: { type: 'boolean', default: false },
            focalPointX: { type: 'number', default: 0.5 },
            focalPointY: { type: 'number', default: 0.5 },
            clientLogoUrl: { type: 'string', default: '' },
            logoHasCircle: { type: 'boolean', default: true },
            region: { type: 'string', default: '' },
            industry: { type: 'string', default: '' },
            subtitle: { type: 'string', default: '' }
        },
        edit: function(props) {
            var attr = props.attributes;

            // 1. Featured Image Syncing
            var featuredMediaId = wp.data.useSelect(function(select) {
                return select('core/editor').getEditedPostAttribute('featured_media');
            });
            var featuredMedia = wp.data.useSelect(function(select) {
                return featuredMediaId ? select('core').getMedia(featuredMediaId) : null;
            }, [featuredMediaId]);

            element.useEffect(function() {
                if (featuredMediaId === undefined) return;
                if (!featuredMediaId) {
                    if (attr.bgImageUrl !== '') {
                        props.setAttributes({ bgImageUrl: '' });
                    }
                } else if (featuredMedia && featuredMedia.source_url) {
                    if (featuredMedia.source_url !== attr.bgImageUrl) {
                        props.setAttributes({ bgImageUrl: featuredMedia.source_url });
                    }
                }
            }, [featuredMediaId, featuredMedia ? featuredMedia.source_url : null, attr.bgImageUrl]);

            // 1b. Post Title Syncing (Default/Fallback)
            var postTitle = wp.data.useSelect(function(select) {
                return select('core/editor').getEditedPostAttribute('title') || '';
            });

            element.useEffect(function() {
                if (!attr.title && postTitle) {
                    props.setAttributes({ title: postTitle });
                }
            }, [postTitle, attr.title]);

            // 2. Post Meta & Taxonomies Syncing
            var isClientPost = wp.data.useSelect(function(select) {
                return select('core/editor').getCurrentPostType() === 'clients';
            });
            var postMeta = wp.data.useSelect(function(select) {
                return select('core/editor').getEditedPostAttribute('meta');
            });

            // Sync focal point attributes back to metadata
            element.useEffect(function() {
                if (!isClientPost || !postMeta) return;
                if (String(attr.focalPointX) !== String(postMeta._e3_client_focal_point_x) ||
                    String(attr.focalPointY) !== String(postMeta._e3_client_focal_point_y)) {
                    wp.data.dispatch('core/editor').editPost({
                        meta: Object.assign({}, postMeta, {
                            _e3_client_focal_point_x: String(attr.focalPointX),
                            _e3_client_focal_point_y: String(attr.focalPointY)
                        })
                    });
                }
            }, [attr.focalPointX, attr.focalPointY, isClientPost, postMeta ? postMeta._e3_client_focal_point_x : undefined, postMeta ? postMeta._e3_client_focal_point_y : undefined]);

            // Sync client logo meta with block attribute
            element.useEffect(function() {
                if (!postMeta) return;
                var metaClientLogo = postMeta._e3_client_logo || '';
                if (metaClientLogo !== attr.clientLogoUrl) {
                    props.setAttributes({ clientLogoUrl: metaClientLogo });
                }
            }, [postMeta ? postMeta._e3_client_logo : undefined, attr.clientLogoUrl]);



            // Sync taxonomies 'region' and 'industry'
            var regionTerms = wp.data.useSelect(function(select) {
                return select('core').getEntityRecords('taxonomy', 'region', { per_page: -1 });
            });
            var industryTerms = wp.data.useSelect(function(select) {
                return select('core').getEntityRecords('taxonomy', 'industry', { per_page: -1 });
            });
            var postRegion = wp.data.useSelect(function(select) {
                return select('core/editor').getEditedPostAttribute('region');
            });
            var postIndustry = wp.data.useSelect(function(select) {
                return select('core/editor').getEditedPostAttribute('industry');
            });

            element.useEffect(function() {
                if (!regionTerms || !postRegion) return;
                if (postRegion.length > 0) {
                    var found = regionTerms.find(function(t) { return t.id === postRegion[0]; });
                    if (found && found.name !== attr.region) {
                        props.setAttributes({ region: found.name });
                    }
                } else if (postRegion.length === 0 && attr.region) {
                    props.setAttributes({ region: '' });
                }
            }, [postRegion ? postRegion[0] : undefined, regionTerms, attr.region]);

            element.useEffect(function() {
                if (!industryTerms || !postIndustry) return;
                if (postIndustry.length > 0) {
                    var found = industryTerms.find(function(t) { return t.id === postIndustry[0]; });
                    if (found && found.name !== attr.industry) {
                        props.setAttributes({ industry: found.name });
                    }
                } else if (postIndustry.length === 0 && attr.industry) {
                    props.setAttributes({ industry: '' });
                }
            }, [postIndustry ? postIndustry[0] : undefined, industryTerms, attr.industry]);

            var inspector = el(InspectorControls, {},
                el(PanelBody, { title: 'Banner Image & Logo', initialOpen: true },
                    isClientPost && el('div', { style: { marginBottom: '15px' } },
                        el('label', { style: { display: 'block', marginBottom: '5px', fontWeight: 'bold' } }, 'Client Logo'),
                        el(MediaSelect, {
                            url: attr.clientLogoUrl,
                            onSelect: function(media) {
                                props.setAttributes({ clientLogoUrl: media.url });
                                if (postMeta) {
                                    wp.data.dispatch('core/editor').editPost({
                                        meta: Object.assign({}, postMeta, {
                                            _e3_client_logo: media.url
                                        })
                                    });
                                }
                            }
                        }),
                        attr.clientLogoUrl && el(Button, {
                            isDestructive: true,
                            style: { marginTop: '10px', marginRight: '10px' },
                            onClick: function() {
                                props.setAttributes({ clientLogoUrl: '' });
                                if (postMeta) {
                                    wp.data.dispatch('core/editor').editPost({
                                        meta: Object.assign({}, postMeta, {
                                            _e3_client_logo: ''
                                        })
                                    });
                                }
                            }
                        }, 'Remove Client Logo'),
                        attr.clientLogoUrl && el(ToggleControl, {
                            label: 'Show Circle around Logo',
                            checked: attr.logoHasCircle !== undefined ? attr.logoHasCircle : true,
                            onChange: function(val) { props.setAttributes({ logoHasCircle: val }); },
                            style: { marginTop: '15px' }
                        })
                    ),
                    el('div', { style: { marginBottom: '15px' } },
                        el('label', { style: { display: 'block', marginBottom: '5px', fontWeight: 'bold' } }, 'Background Image'),
                        el(MediaSelect, {
                            url: attr.bgImageUrl,
                            onSelect: function(media) {
                                props.setAttributes({ bgImageUrl: media.url });
                                wp.data.dispatch('core/editor').editPost({ featured_media: media.id });
                            }
                        }),
                        attr.bgImageUrl && el(Button, {
                            isDestructive: true,
                            style: { marginTop: '10px' },
                            onClick: function() { 
                                props.setAttributes({ bgImageUrl: '' }); 
                                wp.data.dispatch('core/editor').editPost({ featured_media: 0 });
                            }
                        }, 'Remove Background Image')
                    ),
                    attr.bgImageUrl && el('div', { style: { marginBottom: '15px' } },
                        el('label', { style: { display: 'block', marginBottom: '5px', fontWeight: 'bold' } }, 'Focus Point'),
                        el(components.FocalPointPicker, {
                            url: attr.bgImageUrl,
                            value: {
                                x: attr.focalPointX !== undefined ? attr.focalPointX : 0.5,
                                y: attr.focalPointY !== undefined ? attr.focalPointY : 0.5
                            },
                            onChange: function(val) {
                                props.setAttributes({
                                    focalPointX: val.x,
                                    focalPointY: val.y
                                });
                            }
                        })
                    ),
                    el(SelectControl, {
                        label: 'Overlay Color Theme',
                        value: attr.bgOverlayColor,
                        options: [
                            { label: 'Primary Dark Green', value: 'green' },
                            { label: 'Sage Green', value: 'sage' },
                            { label: 'Neutral Black', value: 'black' },
                            { label: 'Midnight Blue', value: 'blue' }
                        ],
                        onChange: function(val) { props.setAttributes({ bgOverlayColor: val }); }
                    }),
                    el(SelectControl, {
                        label: 'Overlay Opacity',
                        value: String(attr.bgOpacity),
                        options: [
                            { label: 'None (0%)', value: '0' },
                            { label: 'Light (30%)', value: '0.3' },
                            { label: 'Medium (60%)', value: '0.6' },
                            { label: 'Dark (85%)', value: '0.85' },
                            { label: 'Extra Dark (95%)', value: '0.95' }
                        ],
                        onChange: function(val) { props.setAttributes({ bgOpacity: parseFloat(val) }); }
                    }),
                    el(SelectControl, {
                        label: 'Fade / Gradient Effect',
                        value: attr.bgFadeType,
                        options: [
                            { label: 'Flat / Uniform Overlay', value: 'flat' },
                            { label: 'Vertical Fade (Darker at Bottom)', value: 'vertical' },
                            { label: 'Horizontal Fade (Darker on Left)', value: 'horizontal' },
                            { label: 'Radial Vignette (Darker Edges)', value: 'vignette' },
                            { label: 'Radial Vignette (Darker Center)', value: 'vignette-center' }
                        ],
                        onChange: function(val) { props.setAttributes({ bgFadeType: val }); }
                    })
                ),
                isClientPost && el(PanelBody, { title: 'Client Options & Metadata', initialOpen: true },
                    el(SelectControl, {
                        label: 'Region',
                        value: postRegion ? (postRegion[0] || '') : '',
                        options: [{ label: 'Select Region...', value: '' }].concat(
                            (regionTerms || []).map(function(term) {
                                  return { label: term.name, value: term.id };
                            })
                        ),
                        onChange: function(val) {
                            var termIds = val ? [parseInt(val)] : [];
                            wp.data.dispatch('core/editor').editPost({ region: termIds });
                        }
                    }),
                    el(SelectControl, {
                        label: 'Virtual',
                        value: postIndustry ? (postIndustry[0] || '') : '',
                        options: [{ label: 'Select Option...', value: '' }].concat(
                            (industryTerms || []).map(function(term) {
                                return { label: term.name, value: term.id };
                            })
                        ),
                        onChange: function(val) {
                            var termIds = val ? [parseInt(val)] : [];
                            wp.data.dispatch('core/editor').editPost({ industry: termIds });
                        }
                    })
                ),
                el(PanelBody, { title: 'Banner Typography & Effects', initialOpen: false },
                    el(SelectControl, {
                        label: 'Text Alignment',
                        value: attr.textAlignment,
                        options: [
                            { label: 'Left', value: 'left' },
                            { label: 'Center', value: 'center' },
                            { label: 'Right', value: 'right' }
                        ],
                        onChange: function(val) { props.setAttributes({ textAlignment: val }); }
                    }),
                    el(SelectControl, {
                        label: 'Text Shadow Density',
                        value: attr.textShadow,
                        options: [
                            { label: 'None', value: 'none' },
                            { label: 'Subtle Shadow', value: 'subtle' },
                            { label: 'Strong Shadow (High Legibility)', value: 'strong' }
                        ],
                        onChange: function(val) { props.setAttributes({ textShadow: val }); }
                    }),
                    el(SelectControl, {
                        label: 'Text Letter Case',
                        value: attr.textCase,
                        options: [
                            { label: 'UPPERCASE', value: 'uppercase' },
                            { label: 'Normal Title Case', value: 'normal' }
                        ],
                        onChange: function(val) { props.setAttributes({ textCase: val }); }
                    }),
                    el(ToggleControl, {
                        label: 'Skew / Tilt Title text (-5°)',
                        checked: attr.textSkew,
                        onChange: function(val) { props.setAttributes({ textSkew: val }); }
                    })
                )
            );

            var logoWrapper = null;
            if (attr.clientLogoUrl) {
                var hasCircle = attr.logoHasCircle !== false;
                var logoClass = 'db-page-hero__logo-wrapper ' + (hasCircle ? 'db-page-hero__logo-wrapper--circle' : 'db-page-hero__logo-wrapper--no-circle');
                logoWrapper = el('div', { className: logoClass },
                    el('img', { src: attr.clientLogoUrl, alt: 'Client Logo', className: 'db-page-hero__logo-img' })
                );
            }

            var heroIntro = null;
            if (isClientPost) {
                var industryMetaVal = (postMeta && postMeta._e3_client_industry) || '';
                var regionLabel = attr.region ? attr.region : '';
                var industryLabel = attr.industry ? attr.industry : industryMetaVal;
                var introTextParts = [];
                if (industryLabel) introTextParts.push(industryLabel);
                if (regionLabel) introTextParts.push(regionLabel);
                var introText = introTextParts.join(' | ');

                heroIntro = el('div', { className: 'db-page-hero__intro', style: { textAlign: attr.textAlignment || 'center' } },
                    el('p', null, introText)
                );
            } else {
                heroIntro = el('div', { className: 'db-page-hero__intro', style: { textAlign: attr.textAlignment || 'center', width: '100%' } },
                    el(RichText, {
                        tagName: 'p',
                        value: attr.subtitle,
                        onChange: function(val) { props.setAttributes({ subtitle: val }); },
                        placeholder: 'Enter Subtitle / Description...'
                    })
                );
            }

            return [
                inspector,
                el('section', { className: 'db-page-hero', style: getBannerStyles(attr) },
                    el('div', { className: 'db-page-hero__container', style: { width: '100%' } },
                        logoWrapper,
                        el('div', null,
                            el(RichText, {
                                tagName: 'h1',
                                className: 'db-page-hero__title',
                                style: getTitleStyles(attr),
                                value: attr.title,
                                onChange: function(val) { props.setAttributes({ title: val }); },
                                placeholder: 'Enter Banner Title...'
                            }),
                            heroIntro
                        )
                    )
                )
            ];
        },
        save: function(props) {
            var attr = props.attributes;

            var logoWrapper = null;
            if (attr.clientLogoUrl) {
                var hasCircle = attr.logoHasCircle !== false;
                var logoClass = 'db-page-hero__logo-wrapper ' + (hasCircle ? 'db-page-hero__logo-wrapper--circle' : 'db-page-hero__logo-wrapper--no-circle');
                logoWrapper = el('div', { className: logoClass },
                    el('img', { src: attr.clientLogoUrl, alt: 'Client Logo', className: 'db-page-hero__logo-img' })
                );
            }

            var heroIntro = null;
            if (attr.region || attr.industry) {
                var regionLabel = attr.region ? attr.region : '';
                var industryLabel = attr.industry ? attr.industry : '';
                var introTextParts = [];
                if (industryLabel) introTextParts.push(industryLabel);
                if (regionLabel) introTextParts.push(regionLabel);
                var introText = introTextParts.join(' | ');

                heroIntro = el('div', { className: 'db-page-hero__intro' },
                    el('p', null, introText)
                );
            } else if (attr.subtitle) {
                heroIntro = el('div', { className: 'db-page-hero__intro' },
                    el('p', null, attr.subtitle)
                );
            }

            return el('section', { className: 'db-page-hero', style: getBannerStyles(attr) },
                el('div', { className: 'db-page-hero__container' },
                    logoWrapper,
                    el('div', null,
                        el(RichText.Content, {
                            tagName: 'h1',
                            className: 'db-page-hero__title',
                            style: getTitleStyles(attr),
                            value: attr.title
                        }),
                        heroIntro
                    )
                )
            );
        }
    });

    // ────────────────────────────────────────────────────────────
    // 10. E3 Video Embed (Case Study Video Section)
    // ────────────────────────────────────────────────────────────
    blocks.registerBlockType('e3es/video-embed', {
        title: 'E3 Video Embed',
        icon: 'video-alt3',
        category: 'layout',
        attributes: {
            title: { type: 'string', default: 'Case Study Video' },
            videoUrl: { type: 'string', default: '' },
            intro: { type: 'string', default: 'This video highlights the energy efficiency improvements and facility upgrades implemented across the district. Watch the case study to see the impact of single-source accountability.' }
        },
        edit: function(props) {
            var attr = props.attributes;
            return [
                el(InspectorControls, {},
                    el(PanelBody, { title: 'Video Settings', initialOpen: true },
                        el(TextControl, {
                            label: 'Section Title',
                            value: attr.title,
                            onChange: function(val) { props.setAttributes({ title: val }); }
                        }),
                        el(TextareaControl, {
                            label: 'Introduction Text',
                            value: attr.intro,
                            onChange: function(val) { props.setAttributes({ intro: val }); }
                        }),
                        el(TextControl, {
                            label: 'Video Embed URL',
                            help: 'Full Vimeo player URL, e.g. https://player.vimeo.com/video/123456',
                            value: attr.videoUrl,
                            onChange: function(val) { props.setAttributes({ videoUrl: val }); }
                        })
                    )
                ),
                el('section', { className: 'db-video-section' },
                    el('h3', { className: 'db-video-section__title' }, attr.title),
                    el(RichText, {
                        tagName: 'p',
                        className: 'db-video-section__intro',
                        value: attr.intro,
                        onChange: function(val) { props.setAttributes({ intro: val }); },
                        placeholder: 'Enter introduction paragraph...'
                    }),
                    el('div', { className: 'db-video-wrapper' },
                        attr.videoUrl
                            ? el('iframe', { src: attr.videoUrl, style: { position: 'absolute', top: 0, left: 0, width: '100%', height: '100%', border: 'none' }, title: attr.title, allow: 'autoplay; fullscreen; picture-in-picture', allowFullScreen: true })
                            : el('p', { style: { color: '#999', position: 'absolute', top: '50%', left: '50%', transform: 'translate(-50%,-50%)' } }, 'Enter a video URL in the sidebar →')
                    )
                )
            ];
        },
        save: function(props) {
            var attr = props.attributes;
            return el('section', { className: 'db-video-section' },
                el('h3', { className: 'db-video-section__title' }, attr.title),
                el(RichText.Content, {
                    tagName: 'p',
                    className: 'db-video-section__intro',
                    value: attr.intro
                }),
                el('div', { className: 'db-video-wrapper' },
                    attr.videoUrl && el('iframe', {
                        src: attr.videoUrl,
                        frameBorder: '0',
                        allow: 'autoplay; fullscreen; picture-in-picture',
                        allowFullScreen: true,
                        title: attr.title
                    })
                )
            );
        }
    });

    // ────────────────────────────────────────────────────────────
    // 11. E3 Project TOC (Jump-to Navigation)
    // ────────────────────────────────────────────────────────────
    blocks.registerBlockType('e3es/project-toc', {
        title: 'E3 Project TOC',
        icon: 'list-view',
        category: 'layout',
        attributes: {
            link1Label: { type: 'string', default: '' },
            link1Href: { type: 'string', default: '' },
            link2Label: { type: 'string', default: '' },
            link2Href: { type: 'string', default: '' },
            link3Label: { type: 'string', default: '' },
            link3Href: { type: 'string', default: '' },
            link4Label: { type: 'string', default: '' },
            link4Href: { type: 'string', default: '' }
        },
        edit: function(props) {
            var attr = props.attributes;
            var linkFields = [];
            for (var i = 1; i <= 4; i++) {
                (function(idx) {
                    linkFields.push(
                        el('div', { style: { marginBottom: '15px', padding: '10px', background: '#f0f0f0', borderRadius: '4px' }, key: idx },
                            el(TextControl, {
                                label: 'Link ' + idx + ' Label',
                                value: attr['link' + idx + 'Label'],
                                onChange: function(val) { var u = {}; u['link' + idx + 'Label'] = val; props.setAttributes(u); }
                            }),
                            el(TextControl, {
                                label: 'Link ' + idx + ' Anchor (#id)',
                                value: attr['link' + idx + 'Href'],
                                onChange: function(val) { var u = {}; u['link' + idx + 'Href'] = val; props.setAttributes(u); }
                            })
                        )
                    );
                })(i);
            }
            return [
                el(InspectorControls, {},
                    el(PanelBody, { title: 'TOC Links', initialOpen: true }, linkFields)
                ),
                el('nav', { className: 'db-toc', style: { display: 'flex', gap: '10px', alignItems: 'center', padding: '12px 0' } },
                    el('span', { className: 'db-toc__label', style: { fontWeight: '700' } }, 'Jump to project:'),
                    attr.link1Label && el('a', { className: 'db-toc__link', style: { color: 'var(--color-primary-green, #2d6a3f)' } }, attr.link1Label),
                    attr.link2Label && el('span', { className: 'db-toc__divider' }, '|'),
                    attr.link2Label && el('a', { className: 'db-toc__link', style: { color: 'var(--color-primary-green, #2d6a3f)' } }, attr.link2Label),
                    attr.link3Label && el('span', { className: 'db-toc__divider' }, '|'),
                    attr.link3Label && el('a', { className: 'db-toc__link', style: { color: 'var(--color-primary-green, #2d6a3f)' } }, attr.link3Label),
                    attr.link4Label && el('span', { className: 'db-toc__divider' }, '|'),
                    attr.link4Label && el('a', { className: 'db-toc__link', style: { color: 'var(--color-primary-green, #2d6a3f)' } }, attr.link4Label)
                )
            ];
        },
        save: function(props) {
            var attr = props.attributes;
            var children = [el('span', { className: 'db-toc__label' }, 'Jump to project:')];
            for (var i = 1; i <= 4; i++) {
                if (attr['link' + i + 'Label']) {
                    if (i > 1) children.push(el('span', { className: 'db-toc__divider' }, '|'));
                    children.push(el('a', { href: attr['link' + i + 'Href'], className: 'db-toc__link' }, attr['link' + i + 'Label']));
                }
            }
            return el('nav', { className: 'db-toc', 'aria-label': 'Table of Contents' }, children);
        }
    });

    // ────────────────────────────────────────────────────────────
    // 12. E3 Project (Eyebrow + Title + Hero Image + InnerBlocks)
    // ────────────────────────────────────────────────────────────
    blocks.registerBlockType('e3es/project', {
        title: 'E3 Project',
        icon: 'welcome-widgets-menus',
        category: 'layout',
        attributes: {
            sectionId: { type: 'string', default: '' },
            eyebrow: { type: 'string', default: 'Project 1' },
            title: { type: 'string', default: '' },
            heroImageUrl: { type: 'string', default: '' },
            focalPointX: { type: 'number', default: 0.5 },
            focalPointY: { type: 'number', default: 0.5 }
        },
        __experimentalLabel: function(attributes, { context }) {
            var eyebrow = attributes.eyebrow ? attributes.eyebrow.trim() : '';
            var title = attributes.title ? attributes.title.replace(/<[^>]+>/g, '').trim() : '';
            var prefix = eyebrow;
            if (eyebrow.toLowerCase().indexOf('project ') === 0) {
                prefix = 'P' + eyebrow.substring(8);
            } else if (eyebrow.toLowerCase().indexOf('project') === 0) {
                prefix = 'P' + eyebrow.substring(7);
            }
            if (prefix && title) {
                return prefix + ': ' + title;
            }
            return title || eyebrow || 'E3 Project';
        },
        edit: function(props) {
            var attr = props.attributes;
            var className = 'project-section' + (props.className ? ' ' + props.className : '');
            return [
                el(InspectorControls, {},
                    el(PanelBody, { title: 'Project Options', initialOpen: true },
                        el(TextControl, {
                            label: 'Section Anchor ID',
                            help: 'Used for TOC jump links, e.g. project-hvac',
                            value: attr.sectionId,
                            onChange: function(val) { props.setAttributes({ sectionId: val }); }
                        }),
                        el('div', { style: { marginTop: '15px' } },
                            el('label', { style: { display: 'block', marginBottom: '5px', fontWeight: 'bold' } }, 'Hero Image'),
                            el(MediaSelect, {
                                url: attr.heroImageUrl,
                                onSelect: function(media) { props.setAttributes({ heroImageUrl: media.url }); }
                            }),
                            attr.heroImageUrl && el(Button, {
                                isDestructive: true, style: { marginTop: '10px' },
                                onClick: function() { props.setAttributes({ heroImageUrl: '' }); }
                            }, 'Remove Image')
                        ),
                        attr.heroImageUrl && el('div', { style: { marginTop: '15px' } },
                            el('label', { style: { display: 'block', marginBottom: '5px', fontWeight: 'bold' } }, 'Focus Point'),
                            el(components.FocalPointPicker, {
                                url: attr.heroImageUrl,
                                value: {
                                    x: attr.focalPointX !== undefined ? attr.focalPointX : 0.5,
                                    y: attr.focalPointY !== undefined ? attr.focalPointY : 0.5
                                },
                                onChange: function(val) {
                                    props.setAttributes({
                                        focalPointX: val.x,
                                        focalPointY: val.y
                                    });
                                }
                            })
                        )
                    )
                ),
                el('div', { className: className, id: attr.sectionId || undefined, style: { '--hero-img': attr.heroImageUrl ? 'url(' + attr.heroImageUrl + ')' : 'none' } },
                    el('div', { className: 'project-section__header' },
                        attr.heroImageUrl && el('div', { className: 'project-section__hero' },
                            el('img', { 
                                src: attr.heroImageUrl, 
                                alt: attr.title, 
                                className: 'project-section__hero-img',
                                style: {
                                    objectPosition: (attr.focalPointX !== undefined ? (attr.focalPointX * 100) : 50) + '% ' + (attr.focalPointY !== undefined ? (attr.focalPointY * 100) : 50) + '%'
                                }
                            }),
                            el('div', { className: 'project-section__mask project-section__mask--left' }),
                            el('div', { className: 'project-section__mask project-section__mask--right' })
                        ),
                        el('div', { className: 'project-section__info' },
                            el(RichText, {
                                tagName: 'span',
                                className: 'project-section__eyebrow',
                                value: attr.eyebrow,
                                onChange: function(val) { props.setAttributes({ eyebrow: val }); },
                                placeholder: 'Project 1'
                            }),
                            el(RichText, {
                                tagName: 'h2',
                                className: 'project-section__title',
                                value: attr.title,
                                onChange: function(val) { props.setAttributes({ title: val }); },
                                placeholder: 'Enter project title...'
                            })
                        )
                    ),
                    el('div', { className: 'project-section__content' },
                        el(InnerBlocks)
                    )
                )
            ];
        },
        save: function(props) {
            var attr = props.attributes;
            var className = 'project-section' + (props.className ? ' ' + props.className : '');
            return el('div', { className: className, id: attr.sectionId || undefined, style: { '--hero-img': attr.heroImageUrl ? 'url(' + attr.heroImageUrl + ')' : 'none' } },
                el('div', { className: 'project-section__header' },
                    attr.heroImageUrl && el('div', { className: 'project-section__hero' },
                        el('img', { 
                            src: attr.heroImageUrl, 
                            alt: attr.title, 
                            className: 'project-section__hero-img',
                            style: {
                                objectPosition: (attr.focalPointX !== undefined ? (attr.focalPointX * 100) : 50) + '% ' + (attr.focalPointY !== undefined ? (attr.focalPointY * 100) : 50) + '%'
                            }
                        }),
                        el('div', { className: 'project-section__mask project-section__mask--left' }),
                        el('div', { className: 'project-section__mask project-section__mask--right' })
                    ),
                    el('div', { className: 'project-section__info' },
                        el(RichText.Content, { tagName: 'span', className: 'project-section__eyebrow', value: attr.eyebrow }),
                        el(RichText.Content, { tagName: 'h2', className: 'project-section__title', value: attr.title })
                    )
                ),
                el('div', { className: 'project-section__content' },
                    el(InnerBlocks.Content)
                )
            );
        }
    });

    wp.blocks.registerBlockStyle('e3es/project', { name: 'default', label: 'Current Style', isDefault: true });
    wp.blocks.registerBlockStyle('e3es/project', { name: 'white-mask', label: 'White Mask' });
    wp.blocks.registerBlockStyle('e3es/project', { name: 'green-texture-behind', label: 'Green Texture Behind Photo' });

    // 13. E3 Project Details (Key-Value Grid)
    // ────────────────────────────────────────────────────────────
    blocks.registerBlockType('e3es/project-details', {
        title: 'E3 Project Details',
        icon: 'info',
        category: 'layout',
        attributes: {
            label1: { type: 'string', default: '' }, value1: { type: 'string', default: '' },
            label2: { type: 'string', default: '' }, value2: { type: 'string', default: '' },
            label3: { type: 'string', default: '' }, value3: { type: 'string', default: '' },
            label4: { type: 'string', default: '' }, value4: { type: 'string', default: '' }
        },
        edit: function(props) {
            var attr = props.attributes;

            // 1. Fetch Taxonomy Terms & Editor Blocks
            var serviceTerms = wp.data.useSelect(function(select) {
                return select('core').getEntityRecords('taxonomy', 'client-services', { per_page: -1 });
            });
            var allBlocks = wp.data.useSelect(function(select) {
                return select('core/block-editor').getBlocks() || [];
            });
            var currentPostServices = wp.data.useSelect(function(select) {
                return select('core/editor').getEditedPostAttribute('client-services');
            });

            // Recursive function to locate all e3es/project-details blocks
            function findProjectDetailsBlocks(blocksList) {
                var found = [];
                blocksList.forEach(function(b) {
                    if (b.name === 'e3es/project-details') {
                        found.push(b);
                    }
                    if (b.innerBlocks && b.innerBlocks.length > 0) {
                        found = found.concat(findProjectDetailsBlocks(b.innerBlocks));
                    }
                });
                return found;
            }

            // 2. Compute the combined choices of all "Project Scope" blocks
            var allDetailsBlocks = findProjectDetailsBlocks(allBlocks);
            var combinedServiceNames = [];
            allDetailsBlocks.forEach(function(b) {
                for (var idx = 1; idx <= 4; idx++) {
                    if (b.attributes['label' + idx] === 'Project Scope') {
                        var val = b.attributes['value' + idx] || '';
                        var parts = val.split(',').map(function(s) { return s.trim(); }).filter(Boolean);
                        parts.forEach(function(p) {
                            if (combinedServiceNames.indexOf(p) === -1) {
                                combinedServiceNames.push(p);
                            }
                        });
                    }
                }
            });

            var combinedIds = [];
            combinedServiceNames.forEach(function(name) {
                var term = (serviceTerms || []).find(function(t) {
                    return t.name.replace(/&amp;/g, '&') === name.replace(/&amp;/g, '&');
                });
                if (term) {
                    combinedIds.push(term.id);
                }
            });

            // 3. Sync combined term IDs back to the post taxonomy
            element.useEffect(function() {
                if (!serviceTerms || !currentPostServices) return;
                var sortedCombined = combinedIds.slice().sort();
                var sortedCurrent = currentPostServices.slice().sort();
                var changed = false;
                if (sortedCombined.length !== sortedCurrent.length) {
                    changed = true;
                } else {
                    for (var i = 0; i < sortedCombined.length; i++) {
                        if (sortedCombined[i] !== sortedCurrent[i]) {
                            changed = true;
                            break;
                        }
                    }
                }
                if (changed) {
                    wp.data.dispatch('core/editor').editPost({ 'client-services': sortedCombined });
                }
            }, [combinedIds.join(','), currentPostServices, serviceTerms]);

            // 4. Build Editor Fields
            var defaultLabels = [
                { label: 'Select Label...', value: '' },
                { label: 'Project Scope', value: 'Project Scope' },
                { label: 'Contract Type', value: 'Contract Type' },
                { label: 'Year Completed', value: 'Year Completed' },
                { label: 'Partnership Program', value: 'Partnership Program' },
                { label: 'Add Custom Label...', value: 'custom_new' }
            ];

            var fields = [];
            for (var i = 1; i <= 4; i++) {
                (function(idx) {
                    var FormTokenField = components.FormTokenField;
                    var labelOptions = defaultLabels.slice();
                    var currentLabel = attr['label' + idx];
                    if (currentLabel && !labelOptions.find(function(o) { return o.value === currentLabel; })) {
                        labelOptions.splice(labelOptions.length - 1, 0, { label: currentLabel, value: currentLabel });
                    }

                    var labelControl = el(SelectControl, {
                        label: 'Label ' + idx,
                        value: currentLabel || '',
                        options: labelOptions,
                        onChange: function(val) {
                            if (val === 'custom_new') {
                                var newLabel = prompt('Enter custom label:');
                                if (newLabel && newLabel.trim()) {
                                    var u = {};
                                    u['label' + idx] = newLabel.trim();
                                    props.setAttributes(u);
                                }
                            } else {
                                var u = {};
                                u['label' + idx] = val;
                                props.setAttributes(u);
                            }
                        }
                    });

                    var valueControl;
                    if (currentLabel === 'Project Scope') {
                        var currentVal = attr['value' + idx] || '';
                        var currentTokens = currentVal.split(',').map(function(s) { return s.trim(); }).filter(Boolean);
                        valueControl = el(FormTokenField, {
                            label: 'Value ' + idx + ' (Scope Services)',
                            value: currentTokens,
                            suggestions: (serviceTerms || []).map(function(t) { return t.name; }),
                            onChange: function(tokens) {
                                var u = {};
                                u['value' + idx] = tokens.join(', ');
                                props.setAttributes(u);
                            }
                        });
                    } else {
                        valueControl = el(TextControl, {
                            label: 'Value ' + idx,
                            value: attr['value' + idx],
                            onChange: function(val) {
                                var u = {};
                                u['value' + idx] = val;
                                props.setAttributes(u);
                            }
                        });
                    }

                    fields.push(
                        el('div', { style: { marginBottom: '15px', padding: '10px', background: idx % 2 === 0 ? '#f9f9f9' : '#fff', border: '1px solid #ddd', borderRadius: '4px' }, key: idx },
                            labelControl,
                            valueControl
                        )
                    );
                })(i);
            }

            return [
                el(InspectorControls, {},
                    el(PanelBody, { title: 'Project Details', initialOpen: true }, fields)
                ),
                el('div', { className: 'project-details' },
                    attr.label1 && el('div', { className: 'project-details__item' },
                        el('span', { className: 'project-details__label' }, attr.label1),
                        el('span', { className: 'project-details__value' }, attr.value1)
                    ),
                    attr.label2 && el('div', { className: 'project-details__item' },
                        el('span', { className: 'project-details__label' }, attr.label2),
                        el('span', { className: 'project-details__value' }, attr.value2)
                    ),
                    attr.label3 && el('div', { className: 'project-details__item' },
                        el('span', { className: 'project-details__label' }, attr.label3),
                        el('span', { className: 'project-details__value' }, attr.value3)
                    ),
                    attr.label4 && el('div', { className: 'project-details__item' },
                        el('span', { className: 'project-details__label' }, attr.label4),
                        el('span', { className: 'project-details__value' }, attr.value4)
                    )
                )
            ];
        },
        save: function(props) {
            var attr = props.attributes;
            var items = [];
            for (var i = 1; i <= 4; i++) {
                if (attr['label' + i]) {
                    items.push(
                        el('div', { className: 'project-details__item', key: i },
                            el('span', { className: 'project-details__label' }, attr['label' + i]),
                            el('span', { className: 'project-details__value' }, attr['value' + i])
                        )
                    );
                }
            }
            return el('div', { className: 'project-details' }, items);
        }
    });

    // ────────────────────────────────────────────────────────────
    // 14. E3 Before & After Comparison
    // ────────────────────────────────────────────────────────────
    blocks.registerBlockType('e3es/before-after', {
        title: 'E3 Before & After',
        icon: 'image-flip-horizontal',
        category: 'layout',
        attributes: {
            title: { type: 'string', default: 'Before & After comparison' },
            beforeImageUrl: { type: 'string', default: '' },
            beforeLabel: { type: 'string', default: 'Before' },
            afterImageUrl: { type: 'string', default: '' },
            afterLabel: { type: 'string', default: 'After' }
        },
        edit: function(props) {
            var attr = props.attributes;
            return [
                el(InspectorControls, {},
                    el(PanelBody, { title: 'Comparison Settings', initialOpen: true },
                        el(TextControl, { label: 'Section Title', value: attr.title, onChange: function(val) { props.setAttributes({ title: val }); } }),
                        el(TextControl, { label: 'Before Label', value: attr.beforeLabel, onChange: function(val) { props.setAttributes({ beforeLabel: val }); } }),
                        el('div', { style: { marginBottom: '15px' } },
                            el('label', { style: { display: 'block', marginBottom: '5px', fontWeight: 'bold' } }, 'Before Image'),
                            el(MediaSelect, { url: attr.beforeImageUrl, onSelect: function(media) { props.setAttributes({ beforeImageUrl: media.url }); } })
                        ),
                        el(TextControl, { label: 'After Label', value: attr.afterLabel, onChange: function(val) { props.setAttributes({ afterLabel: val }); } }),
                        el('div', { style: { marginBottom: '15px' } },
                            el('label', { style: { display: 'block', marginBottom: '5px', fontWeight: 'bold' } }, 'After Image'),
                            el(MediaSelect, { url: attr.afterImageUrl, onSelect: function(media) { props.setAttributes({ afterImageUrl: media.url }); } })
                        )
                    )
                ),
                el('div', { className: 'db-comparison' },
                    el('h4', { className: 'db-comparison__title', style: { marginBottom: '1rem', fontWeight: '700' } }, attr.title),
                    el('div', { className: 'db-comparison__grid', style: { display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '1.5rem' } },
                        el('div', { className: 'db-comparison__card', style: { position: 'relative', overflow: 'hidden', borderRadius: '8px' } },
                            el('span', { className: 'db-comparison__label', style: { position: 'absolute', top: '10px', left: '10px', background: 'rgba(0,0,0,0.6)', color: '#fff', padding: '4px 12px', borderRadius: '4px', fontSize: '0.85rem', zIndex: 1 } }, attr.beforeLabel),
                            attr.beforeImageUrl ? el('img', { src: attr.beforeImageUrl, alt: attr.beforeLabel, className: 'db-comparison__img', style: { width: '100%', height: 'auto' } }) : el('div', { style: { height: '200px', background: '#eee', display: 'flex', alignItems: 'center', justifyContent: 'center' } }, 'Select image →')
                        ),
                        el('div', { className: 'db-comparison__card', style: { position: 'relative', overflow: 'hidden', borderRadius: '8px' } },
                            el('span', { className: 'db-comparison__label', style: { position: 'absolute', top: '10px', left: '10px', background: 'rgba(0,0,0,0.6)', color: '#fff', padding: '4px 12px', borderRadius: '4px', fontSize: '0.85rem', zIndex: 1 } }, attr.afterLabel),
                            attr.afterImageUrl ? el('img', { src: attr.afterImageUrl, alt: attr.afterLabel, className: 'db-comparison__img', style: { width: '100%', height: 'auto' } }) : el('div', { style: { height: '200px', background: '#eee', display: 'flex', alignItems: 'center', justifyContent: 'center' } }, 'Select image →')
                        )
                    )
                )
            ];
        },
        save: function(props) {
            var attr = props.attributes;
            return el('div', { className: 'db-comparison' },
                el('h4', { className: 'db-comparison__title' }, attr.title),
                el('div', { className: 'db-comparison__grid' },
                    el('div', { className: 'db-comparison__card' },
                        el('span', { className: 'db-comparison__label' }, attr.beforeLabel),
                        attr.beforeImageUrl && el('img', { src: attr.beforeImageUrl, alt: attr.beforeLabel, className: 'db-comparison__img' })
                    ),
                    el('div', { className: 'db-comparison__card' },
                        el('span', { className: 'db-comparison__label' }, attr.afterLabel),
                        attr.afterImageUrl && el('img', { src: attr.afterImageUrl, alt: attr.afterLabel, className: 'db-comparison__img' })
                    )
                )
            );
        }
    });

    // ────────────────────────────────────────────────────────────
    // 15. E3 Project Gallery (Image Grid)
    // ────────────────────────────────────────────────────────────
    blocks.registerBlockType('e3es/project-gallery', {
        title: 'E3 Project Gallery',
        icon: 'format-gallery',
        category: 'layout',
        attributes: {
            title: { type: 'string', default: 'Project Gallery' },
            image1Url: { type: 'string', default: '' }, image1Alt: { type: 'string', default: '' },
            image2Url: { type: 'string', default: '' }, image2Alt: { type: 'string', default: '' },
            image3Url: { type: 'string', default: '' }, image3Alt: { type: 'string', default: '' },
            image4Url: { type: 'string', default: '' }, image4Alt: { type: 'string', default: '' },
            image5Url: { type: 'string', default: '' }, image5Alt: { type: 'string', default: '' },
            image6Url: { type: 'string', default: '' }, image6Alt: { type: 'string', default: '' },
            image7Url: { type: 'string', default: '' }, image7Alt: { type: 'string', default: '' },
            image8Url: { type: 'string', default: '' }, image8Alt: { type: 'string', default: '' }
        },
        edit: function(props) {
            var attr = props.attributes;
            var imageSlots = [];
            for (var i = 1; i <= 8; i++) {
                (function(idx) {
                    imageSlots.push(
                        el('div', { style: { marginBottom: '15px', padding: '10px', background: '#f5f5f5', borderRadius: '4px' }, key: idx },
                            el('label', { style: { display: 'block', fontWeight: 'bold', marginBottom: '5px' } }, 'Image ' + idx),
                            el(MediaSelect, {
                                url: attr['image' + idx + 'Url'],
                                onSelect: function(media) { var u = {}; u['image' + idx + 'Url'] = media.url; props.setAttributes(u); }
                            }),
                            attr['image' + idx + 'Url'] && el(TextControl, {
                                label: 'Alt Text',
                                value: attr['image' + idx + 'Alt'],
                                onChange: function(val) { var u = {}; u['image' + idx + 'Alt'] = val; props.setAttributes(u); }
                            }),
                            attr['image' + idx + 'Url'] && el(Button, {
                                isDestructive: true, isSmall: true, style: { marginTop: '5px' },
                                onClick: function() { var u = {}; u['image' + idx + 'Url'] = ''; u['image' + idx + 'Alt'] = ''; props.setAttributes(u); }
                            }, 'Remove')
                        )
                    );
                })(i);
            }

            // Preview thumbnails
            var thumbs = [];
            for (var j = 1; j <= 8; j++) {
                if (attr['image' + j + 'Url']) {
                    thumbs.push(
                        el('div', { className: 'project-gallery__thumbnail', key: j, style: { overflow: 'hidden', borderRadius: '6px', aspectRatio: '4/3' } },
                            el('img', { src: attr['image' + j + 'Url'], alt: attr['image' + j + 'Alt'] || '', className: 'project-gallery__img', style: { width: '100%', height: '100%', objectFit: 'cover' } })
                        )
                    );
                }
            }

            return [
                el(InspectorControls, {},
                    el(PanelBody, { title: 'Gallery Title', initialOpen: true },
                        el(TextControl, { label: 'Gallery Title', value: attr.title, onChange: function(val) { props.setAttributes({ title: val }); } })
                    ),
                    el(PanelBody, { title: 'Gallery Images (up to 8)', initialOpen: false }, imageSlots)
                ),
                el('div', { className: 'project-gallery' },
                    el('h4', { className: 'project-gallery__title', style: { marginBottom: '1rem', fontWeight: '700' } }, attr.title),
                    thumbs.length > 0
                        ? el('div', { className: 'project-gallery__grid', style: { display: 'grid', gridTemplateColumns: 'repeat(4, 1fr)', gap: '12px' } }, thumbs)
                        : el('p', { style: { color: '#999', fontStyle: 'italic' } }, 'Add images in the sidebar →')
                )
            ];
        },
        save: function(props) {
            var attr = props.attributes;
            var thumbs = [];
            for (var i = 1; i <= 8; i++) {
                if (attr['image' + i + 'Url']) {
                    thumbs.push(
                        el('div', { className: 'project-gallery__thumbnail', key: i },
                            el('img', { src: attr['image' + i + 'Url'], alt: attr['image' + i + 'Alt'] || '', className: 'project-gallery__img' })
                        )
                    );
                }
            }
            return el('div', { className: 'project-gallery' },
                el('h4', { className: 'project-gallery__title' }, attr.title),
                el('div', { className: 'project-gallery__grid' }, thumbs)
            );
        }
    });

    // Custom Document Settings Sidebar Panel
    var registerPlugin = window.wp.plugins ? window.wp.plugins.registerPlugin : null;
    var PluginDocumentSettingPanel = window.wp.editPost ? window.wp.editPost.PluginDocumentSettingPanel : null;
    var useSelect = window.wp.data ? window.wp.data.useSelect : null;
    var useDispatch = window.wp.data ? window.wp.data.useDispatch : null;

    var EMPTY_META = {};

    function E3PageSettingsPanel() {
        var postType = wp.data.useSelect(function(select) {
            var store = select('core/editor');
            return store ? store.getCurrentPostType() : null;
        });

        var meta = wp.data.useSelect(function(select) {
            var store = select('core/editor');
            return store ? store.getEditedPostAttribute('meta') : null;
        }) || EMPTY_META;

        var Panel = (window.wp.editor && window.wp.editor.PluginDocumentSettingPanel) || 
                    (window.wp.editPost && window.wp.editPost.PluginDocumentSettingPanel) || 
                    null;
        if (!Panel) return null;

        if (postType === 'services') {
            return el(
                Panel,
                {
                    name: 'e3-page-settings',
                    title: 'E3 Service Page Options',
                    className: 'e3-page-settings-panel'
                },
                el('div', { style: { padding: '10px 0' } },
                    el(TextareaControl, {
                        label: 'Service Card Excerpt',
                        help: 'Short text shown on the service card in the grid layout.',
                        value: meta._e3_service_excerpt || '',
                        onChange: function(val) {
                            wp.data.dispatch('core/editor').editPost({ meta: Object.assign({}, meta, { _e3_service_excerpt: val }) });
                        }
                    }),
                    el('div', { style: { marginTop: '15px' } },
                        el('label', { style: { display: 'block', marginBottom: '8px', fontWeight: 'bold' } }, 'Service Card Image'),
                        el(MediaUpload, {
                            onSelect: function(media) {
                                wp.data.dispatch('core/editor').editPost({ meta: Object.assign({}, meta, { _e3_service_image: media.url }) });
                            },
                            allowedTypes: ['image'],
                            value: meta._e3_service_image,
                            render: function(obj) {
                                if (meta._e3_service_image) {
                                    return el('div', { className: 'media-select-preview-wrapper' },
                                        el('div', { style: { marginBottom: '8px', border: '1px solid #ccc', padding: '5px', background: '#f5f5f5', display: 'flex', justifyContent: 'center', alignItems: 'center', height: '120px', overflow: 'hidden' } },
                                            el('img', { src: meta._e3_service_image, style: { maxWidth: '100%', maxHeight: '100%', objectFit: 'contain', display: 'block' } })
                                        ),
                                        el(Button, { isSecondary: true, isSmall: true, onClick: obj.open, style: { width: '100%', justifyContent: 'center' } }, 'Replace Image')
                                    );
                                } else {
                                    return el(Button, { isSecondary: true, isLarge: true, onClick: obj.open, style: { width: '100%', justifyContent: 'center' } }, 'Select Card Image');
                                }
                            }
                        })
                    )
                )
            );
        }

        if (postType === 'clients') {
            return el(
                Panel,
                {
                    name: 'e3-client-settings',
                    title: 'E3 Client Page Options',
                    className: 'e3-client-settings-panel'
                },
                el('div', { style: { padding: '10px 0' } },
                    el('div', { style: { marginBottom: '15px' } },
                        el('label', { style: { display: 'block', marginBottom: '8px', fontWeight: 'bold' } }, 'Client Logo'),
                        el(MediaUpload, {
                            onSelect: function(media) {
                                wp.data.dispatch('core/editor').editPost({ meta: Object.assign({}, meta, { _e3_client_logo: media.url }) });
                            },
                            allowedTypes: ['image'],
                            value: meta._e3_client_logo,
                            render: function(obj) {
                                if (meta._e3_client_logo) {
                                    return el('div', { className: 'media-select-preview-wrapper' },
                                        el('div', { style: { marginBottom: '8px', border: '1px solid #ccc', padding: '5px', background: '#f5f5f5', display: 'flex', justifyContent: 'center', alignItems: 'center', height: '120px', overflow: 'hidden' } },
                                            el('img', { src: meta._e3_client_logo, style: { maxWidth: '100%', maxHeight: '100%', objectFit: 'contain', display: 'block' } })
                                        ),
                                        el(Button, { isDestructive: true, isSmall: true, onClick: function() { wp.data.dispatch('core/editor').editPost({ meta: Object.assign({}, meta, { _e3_client_logo: '' }) }); }, style: { width: '100%', justifyContent: 'center', marginBottom: '8px' } }, 'Remove Client Logo'),
                                        el(Button, { isSecondary: true, isSmall: true, onClick: obj.open, style: { width: '100%', justifyContent: 'center' } }, 'Replace Image')
                                    );
                                } else {
                                    return el(Button, { isSecondary: true, isLarge: true, onClick: obj.open, style: { width: '100%', justifyContent: 'center' } }, 'Select Client Logo');
                                }
                            }
                        })
                    ),
                    el(TextControl, {
                        label: 'Client Project URL',
                        value: meta._e3_client_project_url || '',
                        onChange: function(val) {
                            wp.data.dispatch('core/editor').editPost({ meta: Object.assign({}, meta, { _e3_client_project_url: val }) });
                        }
                    }),
                    el(TextControl, {
                        label: 'Location (City)',
                        value: meta._e3_client_location || '',
                        onChange: function(val) {
                            wp.data.dispatch('core/editor').editPost({ meta: Object.assign({}, meta, { _e3_client_location: val }) });
                        }
                    }),
                    (function() {
                        var currentYear = meta._e3_client_year || 'Active';
                        var standardYears = ['Active', '2014', '2015', '2016', '2017', '2018', '2019', '2020', '2021', '2022', '2023', '2024', '2025', '2026', '2027', '2028', '2029', '2030'];
                        var options = standardYears.map(function(y) { return { label: y, value: y }; });
                        if (currentYear && standardYears.indexOf(currentYear) === -1) {
                            options.unshift({ label: currentYear, value: currentYear });
                        }
                        return el(SelectControl, {
                            label: 'Completion Year',
                            value: currentYear,
                            options: options,
                            onChange: function(val) {
                                wp.data.dispatch('core/editor').editPost({ meta: Object.assign({}, meta, { _e3_client_year: val }) });
                            }
                        });
                    })(),
                    el(TextControl, {
                        label: 'Contract Type',
                        value: meta._e3_client_contract || '',
                        onChange: function(val) {
                            wp.data.dispatch('core/editor').editPost({ meta: Object.assign({}, meta, { _e3_client_contract: val }) });
                        }
                    }),
                    el(TextControl, {
                        label: 'Project Scope Text',
                        value: meta._e3_client_scope || '',
                        onChange: function(val) {
                            wp.data.dispatch('core/editor').editPost({ meta: Object.assign({}, meta, { _e3_client_scope: val }) });
                        }
                    }),
                    el(ToggleControl, {
                        label: 'Show in Clients Index Page',
                        checked: !!meta._e3_client_show_in_index,
                        onChange: function(val) {
                            wp.data.dispatch('core/editor').editPost({ meta: Object.assign({}, meta, { _e3_client_show_in_index: val }) });
                        }
                    })
                )
            );
        }

        if (postType === 'employees') {
            return el(
                Panel,
                {
                    name: 'e3-employee-settings',
                    title: 'E3 Employee Options',
                    className: 'e3-employee-settings-panel'
                },
                el('div', { style: { padding: '10px 0' } },
                    el(TextControl, {
                        label: 'Job Title / Role',
                        value: meta._e3_employee_role || '',
                        onChange: function(val) {
                            wp.data.dispatch('core/editor').editPost({ meta: Object.assign({}, meta, { _e3_employee_role: val }) });
                        }
                    }),
                    el(TextControl, {
                        label: 'Division',
                        value: meta._e3_employee_division || '',
                        onChange: function(val) {
                            wp.data.dispatch('core/editor').editPost({ meta: Object.assign({}, meta, { _e3_employee_division: val }) });
                        }
                    }),
                    el(TextControl, {
                        label: 'Email',
                        value: meta._e3_employee_email || '',
                        onChange: function(val) {
                            wp.data.dispatch('core/editor').editPost({ meta: Object.assign({}, meta, { _e3_employee_email: val }) });
                        }
                    })
                )
            );
        }

        return null;
    }

    // ── FAQ Section (Thin wrapper — InnerBlocks for headings + paragraphs/lists) ──
    blocks.registerBlockType('e3es/faq-section', {
        title: 'E3 FAQ Section',
        icon: 'editor-help',
        category: 'layout',
        description: 'A styled FAQ wrapper. Add headings (H3) and paragraphs or lists inside — they are automatically styled as an accordion FAQ.',
        attributes: {
            title: {
                type: 'string',
                default: 'Frequently Asked Questions'
            },
            description: {
                type: 'string',
                default: 'process, services, results'
            }
        },
        edit: function(props) {
            var attributes = props.attributes;
            var setAttributes = props.setAttributes;

            function splitKeywordsString(str) {
                var cleanStr = str.replace(/\band\b(?! espcs)/gi, ',');
                var parts = cleanStr.split(',');
                var keywords = [];
                for (var i = 0; i < parts.length; i++) {
                    var part = parts[i].trim();
                    if (!part) continue;
                    
                    part = part.replace(/^and\s+/i, '').trim();
                    var lower = part.toLowerCase();
                    if (lower.indexOf('financing') !== -1) {
                        part = 'Project Financing';
                    } else if (lower.indexOf('hvac') !== -1) {
                        part = 'HVAC Upgrades';
                    } else if (lower.indexOf('lighting') !== -1 || lower.indexOf('led') !== -1) {
                        part = 'LED Lighting';
                    } else if (lower.indexOf('water') !== -1) {
                        part = 'Water Conservation';
                    } else if (lower.indexOf('controls') !== -1 || lower.indexOf('automation') !== -1) {
                        part = 'Smart Controls';
                    } else {
                        part = part.split(' ').map(function(w) {
                            return w.charAt(0).toUpperCase() + w.slice(1);
                        }).join(' ');
                    }
                    
                    if (keywords.indexOf(part) === -1) {
                        keywords.push(part);
                    }
                }
                return keywords;
            }

            function getFaqKeywords(description) {
                if (!description) {
                    return ['Process', 'Services', 'Results'];
                }
                var cleanDesc = description.replace(/<[^>]*>/g, '').trim();
                
                if (/commonly asked questions about our process, services, and results/i.test(cleanDesc)) {
                    return ['Process', 'Services', 'Results'];
                }
                
                var clientMatch = cleanDesc.match(/commonly asked questions about our energy efficiency solutions(?:, including| and)?\s+(.+?)\s+implemented for/i);
                if (clientMatch) {
                    return splitKeywordsString(clientMatch[1]);
                }
                
                if (cleanDesc.indexOf(',') !== -1) {
                    return splitKeywordsString(cleanDesc);
                }
                
                return [cleanDesc.split(' ').map(function(w) {
                    return w.charAt(0).toUpperCase() + w.slice(1);
                }).join(' ')];
            }

            var keywords = getFaqKeywords(attributes.description);

            return [
                el(InspectorControls, {},
                    el(PanelBody, { title: 'FAQ Settings', initialOpen: true },
                        el(TextControl, {
                            label: 'Keywords (comma separated)',
                            value: attributes.description,
                            onChange: function(val) { setAttributes({ description: val }); },
                            help: 'e.g. process, services, results'
                        })
                    )
                ),
                el('section', { className: 'faq-section' },
                    el('div', { className: 'faq-section__container' },
                        el(RichText, {
                            tagName: 'h2',
                            className: 'faq-section__title',
                            value: attributes.title,
                            onChange: function(val) {
                                setAttributes({ title: val });
                            },
                            placeholder: 'Frequently Asked Questions'
                        }),

                        el(InnerBlocks, {
                        allowedBlocks: ['core/heading', 'core/paragraph', 'core/list'],
                        template: [
                            ['core/heading', { level: 3, placeholder: 'FAQ question…' }],
                            ['core/paragraph', { placeholder: 'Answer to the question…' }]
                        ],
                        templateLock: false
                    })
                )
            )
        ];
    },
        save: function(props) {
            var attributes = props.attributes;

            function splitKeywordsString(str) {
                var cleanStr = str.replace(/\band\b(?! espcs)/gi, ',');
                var parts = cleanStr.split(',');
                var keywords = [];
                for (var i = 0; i < parts.length; i++) {
                    var part = parts[i].trim();
                    if (!part) continue;
                    
                    part = part.replace(/^and\s+/i, '').trim();
                    var lower = part.toLowerCase();
                    if (lower.indexOf('financing') !== -1) {
                        part = 'Project Financing';
                    } else if (lower.indexOf('hvac') !== -1) {
                        part = 'HVAC Upgrades';
                    } else if (lower.indexOf('lighting') !== -1 || lower.indexOf('led') !== -1) {
                        part = 'LED Lighting';
                    } else if (lower.indexOf('water') !== -1) {
                        part = 'Water Conservation';
                    } else if (lower.indexOf('controls') !== -1 || lower.indexOf('automation') !== -1) {
                        part = 'Smart Controls';
                    } else {
                        part = part.split(' ').map(function(w) {
                            return w.charAt(0).toUpperCase() + w.slice(1);
                        }).join(' ');
                    }
                    
                    if (keywords.indexOf(part) === -1) {
                        keywords.push(part);
                    }
                }
                return keywords;
            }

            function getFaqKeywords(description) {
                if (!description) {
                    return ['Process', 'Services', 'Results'];
                }
                var cleanDesc = description.replace(/<[^>]*>/g, '').trim();
                
                if (/commonly asked questions about our process, services, and results/i.test(cleanDesc)) {
                    return ['Process', 'Services', 'Results'];
                }
                
                var clientMatch = cleanDesc.match(/commonly asked questions about our energy efficiency solutions(?:, including| and)?\s+(.+?)\s+implemented for/i);
                if (clientMatch) {
                    return splitKeywordsString(clientMatch[1]);
                }
                
                if (cleanDesc.indexOf(',') !== -1) {
                    return splitKeywordsString(cleanDesc);
                }
                
                return [cleanDesc.split(' ').map(function(w) {
                    return w.charAt(0).toUpperCase() + w.slice(1);
                }).join(' ')];
            }

            var keywords = getFaqKeywords(attributes.description);

            return el('section', { className: 'wp-block-e3es-faq-section faq-section' },
                el('div', { className: 'faq-section__container' },
                    el(RichText.Content, {
                        tagName: 'h2',
                        className: 'faq-section__title',
                        value: attributes.title
                    }),

                    el(InnerBlocks.Content)
                )
            );
        },
        deprecated: [
            {
                attributes: {
                    title: {
                        type: 'string',
                        default: 'Frequently Asked Questions'
                    },
                    description: {
                        type: 'string',
                        default: 'Commonly asked questions about our process, services, and results.'
                    }
                },
                save: function(props) {
                    var attributes = props.attributes;
                    return el('section', { className: 'wp-block-e3es-faq-section faq-section' },
                        el('div', { className: 'faq-section__container' },
                            el(RichText.Content, {
                                tagName: 'h2',
                                className: 'faq-section__title',
                                value: attributes.title
                            }),
                            attributes.description ? el('div', { className: 'faq-section__desc-wrapper' },
                                el('svg', {
                                    xmlns: 'http://www.w3.org/2000/svg', viewBox: '0 0 512 512',
                                    width: 20, height: 20, fill: 'var(--color-primary-green, #7DA044)',
                                    style: { flexShrink: 0 }
                                },
                                    el('path', { d: 'M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM169.8 165.3c7.9-22.3 29.1-37.3 52.8-37.3h58.3c34.9 0 63.1 28.3 63.1 63.1 0 22.6-12.1 43.5-31.7 54.8L280 264.4c-.2 13-10.9 23.6-24 23.6c-13.3 0-24-10.7-24-24v-13.5c0-8.6 4.6-16.5 12.1-20.8l44.3-25.4c4.7-2.7 7.6-7.7 7.6-13.1 0-8.4-6.8-15.1-15.1-15.1H222.6c-3.4 0-6.4 2.1-7.5 5.3l-.4 1.2c-4.4 12.5-18.2 19-30.6 14.6s-19-18.2-14.6-30.6l.4-1.2zM224 352a32 32 0 1 1 64 0 32 32 0 1 1 -64 0z' })
                                ),
                                el(RichText.Content, {
                                    tagName: 'p',
                                    className: 'faq-section__desc',
                                    value: attributes.description
                                })
                            ) : null,
                            el(InnerBlocks.Content)
                        )
                    );
                }
            },
            {
                save: function() {
                    return el('section', { className: 'wp-block-e3es-faq-section faq-section' },
                        el('div', { className: 'faq-section__container' },
                            el(InnerBlocks.Content)
                        )
                    );
                }
            }
        ]
    });

    if (registerPlugin && PluginDocumentSettingPanel) {
        registerPlugin('e3-page-settings-plugin', {
            render: E3PageSettingsPanel
        });
    }

    // ── Testimonial Picker — Dynamic block, server-rendered ──────────────────
    blocks.registerBlockType('e3es/testimonial-picker', {
        title: 'E3 Testimonial Picker',
        icon: 'format-quote',
        category: 'layout',
        supports: { inserter: false },
        attributes: {
            testimonialId: { type: 'number', default: 0 }
        },
        edit: function(props) {
            var attr = props.attributes;
            var useState = element.useState;
            var useEffect = element.useEffect;

            var filtersState = useState(null);
            var filters = filtersState[0]; var setFilters = filtersState[1];

            var searchState = useState('');
            var search = searchState[0]; var setSearch = searchState[1];

            var filterPersonState = useState('');
            var filterPerson = filterPersonState[0]; var setFilterPerson = filterPersonState[1];

            var filterServiceState = useState('');
            var filterService = filterServiceState[0]; var setFilterService = filterServiceState[1];

            var filterIndustryState = useState('');
            var filterIndustry = filterIndustryState[0]; var setFilterIndustry = filterIndustryState[1];

            var filterRegionState = useState('');
            var filterRegion = filterRegionState[0]; var setFilterRegion = filterRegionState[1];

            var filterKeywordState = useState('');
            var filterKeyword = filterKeywordState[0]; var setFilterKeyword = filterKeywordState[1];

            var resultsState = useState([]);
            var results = resultsState[0]; var setResults = resultsState[1];

            var loadingState = useState(false);
            var loading = loadingState[0]; var setLoading = loadingState[1];

            var selectedState = useState(null);
            var selected = selectedState[0]; var setSelected = selectedState[1];

            var debounceRef = element.useRef(null);

            // Load filter options once
            useEffect(function() {
                wp.apiFetch({ path: '/e3es/v1/testimonials/filters' }).then(function(data) {
                    setFilters(data);
                }).catch(function() { setFilters({}); });
            }, []);

            // Load selected testimonial data on mount
            useEffect(function() {
                if (attr.testimonialId) {
                    wp.apiFetch({ path: '/e3es/v1/testimonials/search' }).then(function(data) {
                        var match = data.find(function(t) { return t.id === attr.testimonialId; });
                        if (match) {
                            setSelected(match);
                        }
                    });
                }
            }, []);

            // Debounced search with filter params
            useEffect(function() {
                if (selected) return;
                if (debounceRef.current) clearTimeout(debounceRef.current);
                debounceRef.current = setTimeout(function() {
                    setLoading(true);
                    var params = [];
                    if (search)         params.push('search='    + encodeURIComponent(search));
                    if (filterPerson)   params.push('person_id=' + encodeURIComponent(filterPerson));
                    if (filterService)  params.push('service='   + encodeURIComponent(filterService));
                    if (filterIndustry) params.push('industry='  + encodeURIComponent(filterIndustry));
                    if (filterRegion)   params.push('region='    + encodeURIComponent(filterRegion));
                    if (filterKeyword)  params.push('keyword='   + encodeURIComponent(filterKeyword));
                    var path = '/e3es/v1/testimonials/search' + (params.length ? '?' + params.join('&') : '');
                    wp.apiFetch({ path: path }).then(function(data) {
                        setResults(data);
                        setLoading(false);
                    }).catch(function() {
                        setResults([]);
                        setLoading(false);
                    });
                }, 300);
                return function() { if (debounceRef.current) clearTimeout(debounceRef.current); };
            }, [selected, search, filterPerson, filterService, filterIndustry, filterRegion, filterKeyword]);

            function selectTestimonial(item) {
                props.setAttributes({ testimonialId: item.id });
                setSelected(item);
            }

            function clearSelection() {
                props.setAttributes({ testimonialId: 0 });
                setSelected(null);
                setSearch('');
                setFilterPerson(''); setFilterService(''); setFilterIndustry('');
                setFilterRegion(''); setFilterKeyword('');
            }

            // ── If a testimonial is selected, show preview ──────────────
            if (selected) {
                return el('div', {
                    className: 'testimonial-picker testimonial-picker--editor',
                    style: {
                        background: 'linear-gradient(135deg, #f8faf5, #eef3e6)',
                        border: '2px solid var(--color-primary-green, #5c8a1e)',
                        borderRadius: '8px',
                        padding: '24px',
                        position: 'relative'
                    }
                },
                    el('div', {
                        style: {
                            position: 'absolute', top: '8px', right: '8px',
                            display: 'flex', gap: '6px', alignItems: 'center'
                        }
                    },
                        selected.editUrl && el('a', {
                            href: selected.editUrl,
                            target: '_blank',
                            rel: 'noopener noreferrer',
                            style: {
                                display: 'inline-flex', alignItems: 'center', gap: '4px',
                                fontSize: '11px', fontWeight: '600',
                                color: 'var(--color-primary-green, #5c8a1e)',
                                textDecoration: 'none', padding: '4px 8px',
                                border: '1px solid var(--color-primary-green, #5c8a1e)',
                                borderRadius: '4px', background: '#fff'
                            }
                        }, 'Edit Testimonial ↗'),
                        el(Button, {
                            isSmall: true, isSecondary: true,
                            onClick: clearSelection,
                            icon: 'dismiss'
                        }, 'Change')
                    ),
                    el('div', {
                        style: {
                            fontSize: '11px', textTransform: 'uppercase', letterSpacing: '1.5px',
                            color: 'var(--color-primary-green, #5c8a1e)', fontWeight: '700',
                            marginBottom: '12px'
                        }
                    }, 'Testimonial'),
                    el('blockquote', {
                        style: {
                            margin: '0 0 16px 0', padding: '0 0 0 16px',
                            borderLeft: '3px solid var(--color-primary-green, #5c8a1e)',
                            fontStyle: 'italic', fontSize: '15px', lineHeight: '1.6',
                            color: '#333'
                        }
                    }, selected.quote || '(No quote)'),
                    el('div', {
                        style: { display: 'flex', alignItems: 'center', gap: '12px' }
                    },
                        selected.photoUrl
                            ? el('img', {
                                src: selected.photoUrl,
                                alt: selected.personName || '',
                                style: {
                                    width: '48px', height: '48px', borderRadius: '50%',
                                    objectFit: 'cover', border: '2px solid var(--color-primary-green, #5c8a1e)'
                                }
                            })
                            : el('div', {
                                style: {
                                    width: '48px', height: '48px', borderRadius: '50%',
                                    background: '#ddd', display: 'flex', alignItems: 'center',
                                    justifyContent: 'center', fontSize: '18px', color: '#999',
                                    flexShrink: 0
                                }
                            }, '👤'),
                        el('div', {},
                            selected.personName && el('strong', {
                                style: { display: 'block', fontSize: '14px', color: '#1a2a1e' }
                            }, selected.personName),
                            selected.personTitle && el('span', {
                                style: { fontSize: '12px', color: '#666' }
                            }, selected.personTitle)
                        )
                    ),
                    (selected.service || selected.industry || selected.region) && el('div', {
                        style: {
                            marginTop: '12px', display: 'flex', gap: '8px', flexWrap: 'wrap'
                        }
                    },
                        selected.service && el('span', {
                            style: {
                                background: 'rgba(92,138,30,0.12)', color: '#3d6013',
                                padding: '2px 8px', borderRadius: '4px', fontSize: '11px',
                                fontWeight: '600'
                            }
                        }, selected.service),
                        selected.industry && el('span', {
                            style: {
                                background: 'rgba(92,138,30,0.12)', color: '#3d6013',
                                padding: '2px 8px', borderRadius: '4px', fontSize: '11px',
                                fontWeight: '600'
                            }
                        }, selected.industry),
                        selected.region && el('span', {
                            style: {
                                background: 'rgba(92,138,30,0.12)', color: '#3d6013',
                                padding: '2px 8px', borderRadius: '4px', fontSize: '11px',
                                fontWeight: '600'
                            }
                        }, selected.region)
                    )
                );
            }

            // ── Build filter dropdown options ────────────────────────────
            var personOptions  = [{ value: '', label: '— Any Person —' }];
            var serviceOptions = [{ value: '', label: '— Any Service —' }];
            var industryOptions= [{ value: '', label: '— Any Industry —' }];
            var regionOptions  = [{ value: '', label: '— Any Region —' }];
            var keywordOptions = [{ value: '', label: '— Any Keyword —' }];

            if (filters) {
                (filters.people   || []).forEach(function(p) { personOptions.push({ value: String(p.id), label: p.label }); });
                (filters.service  || []).forEach(function(v) { serviceOptions.push({ value: v, label: v }); });
                (filters.industry || []).forEach(function(v) { industryOptions.push({ value: v, label: v }); });
                (filters.region   || []).forEach(function(v) { regionOptions.push({ value: v, label: v }); });
                (filters.keyword  || []).forEach(function(v) { keywordOptions.push({ value: v, label: v }); });
            }

            // ── Search / picker UI ──────────────────────────────────────
            return el('div', {
                className: 'testimonial-picker testimonial-picker--search',
                style: {
                    border: '1px dashed #bbb', borderRadius: '8px',
                    padding: '24px', background: '#fafafa'
                }
            },
                el('div', {
                    style: {
                        fontSize: '11px', textTransform: 'uppercase', letterSpacing: '1.5px',
                        color: 'var(--color-primary-green, #5c8a1e)', fontWeight: '700',
                        marginBottom: '12px'
                    }
                }, 'Select a Testimonial'),
                // ── Filter dropdowns ──────────────────────────────────
                el('div', { style: { display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(160px, 1fr))', gap: '8px', marginBottom: '12px' } },
                    el(SelectControl, { label: 'Person',   value: filterPerson,   options: personOptions,   onChange: setFilterPerson }),
                    el(SelectControl, { label: 'Service',  value: filterService,  options: serviceOptions,  onChange: setFilterService }),
                    el(SelectControl, { label: 'Industry', value: filterIndustry, options: industryOptions, onChange: setFilterIndustry }),
                    el(SelectControl, { label: 'Region',   value: filterRegion,   options: regionOptions,   onChange: setFilterRegion }),
                    el(SelectControl, { label: 'Keyword',  value: filterKeyword,  options: keywordOptions,  onChange: setFilterKeyword })
                ),
                el(TextControl, {
                    label: 'Search by name, quote, or keyword',
                    value: search,
                    onChange: setSearch,
                    placeholder: 'Start typing to search…'
                }),
                loading && el('div', {
                    style: { textAlign: 'center', padding: '12px', color: '#999' }
                }, 'Searching…'),
                !loading && results.length === 0 && el('div', {
                    style: {
                        textAlign: 'center', padding: '24px', color: '#999',
                        fontSize: '13px'
                    }
                }, search || filterPerson || filterService || filterIndustry || filterRegion || filterKeyword
                    ? 'No testimonials match your filters.'
                    : 'No testimonials yet. Create some first!'),
                !loading && results.length > 0 && el('div', {
                    style: {
                        maxHeight: '320px', overflowY: 'auto',
                        border: '1px solid #e0e0e0', borderRadius: '4px',
                        background: '#fff'
                    }
                },
                    results.map(function(item) {
                        return el('div', {
                            key: item.id,
                            style: { borderBottom: '1px solid #f0f0f0', padding: '10px 14px' }
                        },
                            el('div', {
                                style: { display: 'flex', alignItems: 'center', gap: '10px', marginBottom: '4px' }
                            },
                                item.photoUrl
                                    ? el('img', {
                                        src: item.photoUrl,
                                        alt: '',
                                        style: {
                                            width: '32px', height: '32px', borderRadius: '50%',
                                            objectFit: 'cover', flexShrink: 0
                                        }
                                    })
                                    : el('div', {
                                        style: {
                                            width: '32px', height: '32px', borderRadius: '50%',
                                            background: '#e0e0e0', display: 'flex',
                                            alignItems: 'center', justifyContent: 'center',
                                            fontSize: '14px', color: '#999', flexShrink: 0
                                        }
                                    }, '👤'),
                                el('div', { style: { flex: 1, minWidth: 0 } },
                                    el('strong', { style: { fontSize: '13px' } },
                                        item.personName || item.title || 'Testimonial #' + item.id
                                    ),
                                    item.personTitle && el('span', {
                                        style: { fontSize: '11px', color: '#888', marginLeft: '6px' }
                                    }, item.personTitle)
                                ),
                                item.editUrl && el('a', {
                                    href: item.editUrl,
                                    target: '_blank',
                                    rel: 'noopener noreferrer',
                                    onClick: function(e) { e.stopPropagation(); },
                                    style: {
                                        fontSize: '10px', color: 'var(--color-primary-green, #5c8a1e)',
                                        textDecoration: 'none', padding: '2px 6px',
                                        border: '1px solid var(--color-primary-green, #5c8a1e)',
                                        borderRadius: '3px', flexShrink: 0, whiteSpace: 'nowrap'
                                    }
                                }, 'Edit ↗')
                            ),
                            el('div', {
                                style: {
                                    fontSize: '12px', color: '#555', lineHeight: '1.4',
                                    overflow: 'hidden', textOverflow: 'ellipsis',
                                    whiteSpace: 'nowrap', maxWidth: '100%'
                                }
                            }, '"' + (item.quote || '').substring(0, 120) + (item.quote && item.quote.length > 120 ? '…' : '') + '"'),
                            (item.service || item.industry || item.region) && el('div', {
                                style: { marginTop: '4px', display: 'flex', gap: '6px', flexWrap: 'wrap' }
                            },
                                item.service && el('span', {
                                    style: {
                                        background: '#eef3e6', padding: '1px 6px',
                                        borderRadius: '3px', fontSize: '10px', color: '#3d6013'
                                    }
                                }, item.service),
                                item.industry && el('span', {
                                    style: {
                                        background: '#eef3e6', padding: '1px 6px',
                                        borderRadius: '3px', fontSize: '10px', color: '#3d6013'
                                    }
                                }, item.industry),
                                item.region && el('span', {
                                    style: {
                                        background: '#eef3e6', padding: '1px 6px',
                                        borderRadius: '3px', fontSize: '10px', color: '#3d6013'
                                    }
                                }, item.region)
                            )
                        );
                    })
                )
            );
        },
        save: function() {
            // Dynamic block — rendered server-side by PHP
            return null;
        }
    });

    // ── Team Directory — Dynamic block, server-rendered ──────────────────────
    blocks.registerBlockType('e3es/team-directory', {
        title: 'E3 Team Directory',
        icon: 'groups',
        category: 'layout',
        attributes: {},
        edit: function(props) {
            var useState = element.useState;
            var useEffect = element.useEffect;

            var countState = useState(null);
            var count = countState[0]; var setCount = countState[1];

            useEffect(function() {
                wp.apiFetch({ path: '/wp/v2/employees?per_page=1&_fields=id' }).then(function(data, status, xhr) {
                    // Get total from response
                    setCount(data.length >= 1 ? 'Loaded' : '0');
                }).catch(function() { setCount('?'); });
                // Also try to get the total header
                fetch('/wp-json/wp/v2/employees?per_page=1&_fields=id').then(function(r) {
                    var total = r.headers.get('X-WP-Total');
                    if (total) setCount(parseInt(total, 10));
                }).catch(function() {});
            }, []);

            return el('div', {
                className: 'e3-team-directory-placeholder',
                style: {
                    border: '2px dashed var(--color-primary-green, #5c8a1e)',
                    borderRadius: '8px',
                    padding: '32px 24px',
                    background: 'linear-gradient(135deg, #f8faf5, #eef3e6)',
                    textAlign: 'center'
                }
            },
                el('div', {
                    style: {
                        fontSize: '32px', marginBottom: '8px'
                    }
                }, '👥'),
                el('div', {
                    style: {
                        fontSize: '11px', textTransform: 'uppercase', letterSpacing: '1.5px',
                        color: 'var(--color-primary-green, #5c8a1e)', fontWeight: '700',
                        marginBottom: '8px'
                    }
                }, 'Team Directory'),
                el('p', {
                    style: {
                        fontSize: '14px', color: '#555', margin: '0 0 6px 0', lineHeight: '1.5'
                    }
                }, count !== null
                    ? (typeof count === 'number' ? count + ' team members' : 'Team members loaded')
                    : 'Loading…'
                ),
                el('p', {
                    style: {
                        fontSize: '12px', color: '#888', margin: '0 0 16px 0', lineHeight: '1.5',
                        maxWidth: '500px', marginLeft: 'auto', marginRight: 'auto'
                    }
                }, 'This block renders the full team grid on the frontend. To add, edit, or reorder team members, use the Employees admin.'),
                el('div', { style: { display: 'flex', gap: '10px', justifyContent: 'center', flexWrap: 'wrap' } },
                    el('a', {
                        href: '/wp-admin/edit.php?post_type=employees',
                        target: '_blank',
                        style: {
                            display: 'inline-flex', alignItems: 'center', gap: '6px',
                            padding: '8px 16px', background: 'var(--color-primary-green, #5c8a1e)',
                            color: '#fff', borderRadius: '4px', textDecoration: 'none',
                            fontSize: '13px', fontWeight: '600'
                        }
                    }, '✏️ Manage Team Members'),
                    el('a', {
                        href: '/wp-admin/edit.php?post_type=employees&page=e3-reorder-team',
                        target: '_blank',
                        style: {
                            display: 'inline-flex', alignItems: 'center', gap: '6px',
                            padding: '8px 16px', background: '#fff',
                            color: 'var(--color-primary-green, #5c8a1e)',
                            border: '1px solid var(--color-primary-green, #5c8a1e)',
                            borderRadius: '4px', textDecoration: 'none',
                            fontSize: '13px', fontWeight: '600'
                        }
                    }, '↕️ Reorder Team')
                )
            );
        },
        save: function() {
            // Dynamic block — rendered server-side by PHP
            return null;
        }
    });

    // ── Full-Width Testimonial — inline blockquote with avatar, byline + optional case study link ──
    blocks.registerBlockType('e3es/full-width-testimonial', {
        title: 'E3 Full-Width Testimonial',
        icon: 'format-quote',
        category: 'layout',
        supports: { inserter: false },
        description: 'A wide testimonial blockquote with avatar, byline, and optional case study link. Use inside two-column or standalone.',
        attributes: {
            quote:        { type: 'string',  default: '' },
            byline:       { type: 'string',  default: '' },
            photoUrl:     { type: 'string',  default: '' },
            caseStudyUrl: { type: 'string',  default: '' },
            caseStudyText:{ type: 'string',  default: 'Read Case Study' },
            bgStyle:      { type: 'string',  default: 'white' },  // white | light
        },
        edit: function(props) {
            var attr = props.attributes;
            var bgMap = { white: '#fff', light: '#F4F6F8' };
            var bg = bgMap[attr.bgStyle] || '#fff';
            return [
                el(InspectorControls, {},
                    el(PanelBody, { title: 'Testimonial Content', initialOpen: true },
                        el(TextareaControl, { label: 'Quote', value: attr.quote, rows: 4, onChange: function(v) { props.setAttributes({ quote: v }); } }),
                        el(TextControl,     { label: 'Byline (name, title, org)', value: attr.byline, onChange: function(v) { props.setAttributes({ byline: v }); } }),
                        el('label', { style: { display: 'block', fontWeight: 'bold', marginBottom: '5px' } }, 'Avatar Photo'),
                        el(MediaSelect, { url: attr.photoUrl, onSelect: function(m) { props.setAttributes({ photoUrl: m.url }); } }),
                        el(TextControl, { label: 'Case Study URL (optional)', value: attr.caseStudyUrl, onChange: function(v) { props.setAttributes({ caseStudyUrl: v }); } }),
                        el(TextControl, { label: 'Case Study Link Text', value: attr.caseStudyText, onChange: function(v) { props.setAttributes({ caseStudyText: v }); } })
                    ),
                    el(PanelBody, { title: 'Background', initialOpen: false },
                        el(SelectControl, { label: 'Background', value: attr.bgStyle, options: [ { value: 'white', label: 'White' }, { value: 'light', label: 'Light Grey' } ], onChange: function(v) { props.setAttributes({ bgStyle: v }); } })
                    )
                ),
                el('div', { className: 'full-width-testimonial', style: { background: bg, borderLeft: '4px solid var(--color-primary-green,#215734)', padding: '2rem', display: 'flex', gap: '1.5rem', alignItems: 'center', maxWidth: '1200px', margin: '2rem auto', borderRadius: '2px' } },
                    attr.photoUrl && el('div', { style: { width: '70px', height: '70px', borderRadius: '50%', overflow: 'hidden', border: '2px solid var(--color-primary-green,#215734)', flexShrink: 0 } },
                        el('img', { src: attr.photoUrl, style: { width: '100%', height: '100%', objectFit: 'cover' } })
                    ),
                    el('div', { style: { flex: 1 } },
                        el('div', { style: { fontStyle: 'italic', fontSize: '1.1rem', lineHeight: '1.6', color: '#333', marginBottom: '0.75rem' } }, attr.quote || 'Enter testimonial quote in the sidebar →'),
                        el('div', { style: { fontSize: '0.9rem', fontWeight: '700', color: 'var(--color-primary-dark,#0e1b2b)' } }, '— ' + (attr.byline || 'Name, Title')),
                        attr.caseStudyUrl && el('a', { href: attr.caseStudyUrl, style: { color: 'var(--color-primary-dark,#0e1b2b)', fontWeight: '700', fontSize: '0.9rem', textDecoration: 'underline', display: 'inline-block', marginTop: '0.5rem' } }, attr.caseStudyText)
                    )
                )
            ];
        },
        save: function() { return null; }  // PHP-rendered
    });

    // ── Rep Contact Card — "Meet [Name]" regional rep sidebar card ──────────────
    blocks.registerBlockType('e3es/rep-contact-card', {
        title: 'E3 Rep Contact Card',
        icon: 'businessperson',
        category: 'layout',
        description: 'A "Meet [Name]" regional contact card with photo, role, quote, and contact buttons.',
        attributes: {
            name:       { type: 'string', default: '' },
            role:       { type: 'string', default: '' },
            bio:        { type: 'string', default: '' },
            photoUrl:   { type: 'string', default: '' },
            emailLabel: { type: 'string', default: 'Email' },
            emailHref:  { type: 'string', default: '' },
            callLabel:  { type: 'string', default: 'Schedule a Call' },
            callHref:   { type: 'string', default: '' },
        },
        edit: function(props) {
            var attr = props.attributes;
            return [
                el(InspectorControls, {},
                    el(PanelBody, { title: 'Rep Info', initialOpen: true },
                        el(TextControl, { label: 'Name', value: attr.name, onChange: function(v) { props.setAttributes({ name: v }); } }),
                        el(TextControl, { label: 'Role / Territory', value: attr.role, onChange: function(v) { props.setAttributes({ role: v }); } }),
                        el(TextareaControl, { label: 'Bio / Quote', value: attr.bio, rows: 3, onChange: function(v) { props.setAttributes({ bio: v }); } }),
                        el('label', { style: { display: 'block', fontWeight: 'bold', marginBottom: '5px' } }, 'Photo'),
                        el(MediaSelect, { url: attr.photoUrl, onSelect: function(m) { props.setAttributes({ photoUrl: m.url }); } })
                    ),
                    el(PanelBody, { title: 'Contact Buttons', initialOpen: false },
                        el(TextControl, { label: 'Email Button Label', value: attr.emailLabel, onChange: function(v) { props.setAttributes({ emailLabel: v }); } }),
                        el(TextControl, { label: 'Email href (mailto:…)', value: attr.emailHref, onChange: function(v) { props.setAttributes({ emailHref: v }); } }),
                        el(TextControl, { label: 'Call Button Label', value: attr.callLabel, onChange: function(v) { props.setAttributes({ callLabel: v }); } }),
                        el(TextControl, { label: 'Call href (tel:…)', value: attr.callHref, onChange: function(v) { props.setAttributes({ callHref: v }); } })
                    )
                ),
                el('div', { className: 'rep-contact-card', style: { background: '#fff', boxShadow: '0 4px 15px rgba(0,0,0,0.08)', padding: '2rem', textAlign: 'center', borderLeft: '4px solid var(--color-primary-green,#215734)', maxWidth: '320px' } },
                    attr.photoUrl && el('div', { style: { width: '130px', height: '130px', borderRadius: '50%', overflow: 'hidden', border: '3px solid var(--color-primary-green,#215734)', margin: '0 auto 1rem' } },
                        el('img', { src: attr.photoUrl, style: { width: '100%', height: '100%', objectFit: 'cover' } })
                    ),
                    el('h3', { style: { color: 'var(--color-primary-dark,#0e1b2b)', fontSize: '1.4rem', marginBottom: '0.25rem' } }, 'Meet ' + (attr.name || 'Rep')),
                    el('p', { style: { color: 'var(--color-primary-green,#215734)', fontSize: '0.85rem', fontWeight: '700', textTransform: 'uppercase', marginBottom: '1rem' } }, attr.role || 'Territory'),
                    attr.bio && el('p', { style: { fontStyle: 'italic', fontSize: '0.9rem', lineHeight: '1.5', color: '#555', marginBottom: '1rem' } }, attr.bio),
                    el('div', { style: { display: 'flex', gap: '0.5rem', justifyContent: 'center' } },
                        el('a', { href: attr.emailHref || '#', className: 'btn btn--primary', style: { padding: '0.5rem 1rem', fontSize: '0.9rem' } }, attr.emailLabel),
                        el('a', { href: attr.callHref || '#', className: 'btn btn--outline', style: { padding: '0.5rem 1rem', fontSize: '0.9rem', borderColor: 'var(--color-primary-dark,#0e1b2b)', color: 'var(--color-primary-dark,#0e1b2b)' } }, attr.callLabel)
                    )
                )
            ];
        },
        save: function() { return null; }  // PHP-rendered
    });

    // ── Region Showcase — scrollable client card slider section ────────────────
    blocks.registerBlockType('e3es/region-showcase', {
        title: 'E3 Client Cards',
        icon: 'slides',
        category: 'layout',
        description: 'A horizontally-scrolling client project card slider for regional pages. Add a heading and then use inner blocks for content.',
        attributes: {
            heading:  { type: 'string', default: 'Featured Projects' },
            bgStyle:  { type: 'string', default: 'white' },
        },
        edit: function(props) {
            var attr = props.attributes;
            var bgMap = { white: 'var(--color-bg-white,#fff)', light: 'var(--color-bg-light,#F4F6F8)' };
            return [
                el(InspectorControls, {},
                    el(PanelBody, { title: 'Section Settings', initialOpen: true },
                        el(TextControl, { label: 'Section Heading', value: attr.heading, onChange: function(v) { props.setAttributes({ heading: v }); } }),
                        el(SelectControl, { label: 'Background', value: attr.bgStyle, options: [ { value: 'white', label: 'White' }, { value: 'light', label: 'Light Grey' } ], onChange: function(v) { props.setAttributes({ bgStyle: v }); } })
                    )
                ),
                el('section', { className: 'region-showcase', style: { background: bgMap[attr.bgStyle] || '#fff', padding: '4rem 2rem' } },
                    el('div', { className: 'region-showcase__container', style: { maxWidth: '1200px', margin: '0 auto' } },
                        el('h2', { style: { textAlign: 'center', marginBottom: '2rem', color: 'var(--color-primary-dark,#0e1b2b)' } }, attr.heading),
                        el('div', { style: { border: '2px dashed #ccc', borderRadius: '4px', padding: '1rem', background: 'rgba(0,0,0,0.02)' } },
                            el('p', { style: { color: '#999', fontSize: '0.85rem', margin: '0 0 0.5rem', textAlign: 'center' } }, '↓ Inner Blocks — Add core/columns or E3 Client Card blocks below ↓'),
                            el(InnerBlocks, {
                                allowedBlocks: ['core/columns', 'e3es/mini-case-study', 'core/group'],
                                template: [],
                                templateLock: false
                            })
                        )
                    )
                )
            ];
        },
        save: function(props) {
            return el(InnerBlocks.Content);
        }
    });
    // 30. Client Slider
    blocks.registerBlockType('e3es/client-slider', {
        title: 'Client Slider',
        icon: 'slides',
        category: 'layout',
        edit: function(props) {
            return el('div', { className: 'client-slider-block', style: { border: '2px dashed #ccc', padding: '20px' } },
                el('h3', null, 'Client Slider (Carousel)'),
                el(InnerBlocks, { allowedBlocks: ['e3es/client-slide'] })
            );
        },
        save: function(props) {
            return el('div', { className: 'client-slider-block' },
                el(InnerBlocks.Content, null)
            );
        }
    });

    blocks.registerBlockType('e3es/client-slide', {
        title: 'Client Slide',
        icon: 'format-image',
        category: 'layout',
        parent: ['e3es/client-slider'],
        attributes: {
            clientName: { type: 'string', default: '' },
            kpi1Label: { type: 'string', default: '' },
            kpi1Value: { type: 'string', default: '' },
            kpi2Label: { type: 'string', default: '' },
            kpi2Value: { type: 'string', default: '' },
            kpi3Label: { type: 'string', default: '' },
            kpi3Value: { type: 'string', default: '' },
            link: { type: 'string', default: '' },
            image: { type: 'string', default: '' }
        },
        edit: function(props) {
            var attributes = props.attributes;
            var setAttributes = props.setAttributes;
            return el('div', { style: { border: '1px solid #eee', padding: '15px', marginBottom: '15px', background: '#fafafa' } },
                el(TextControl, { label: 'Client Name', value: attributes.clientName, onChange: function(val) { setAttributes({ clientName: val }); } }),
                el(TextControl, { label: 'Link (URL)', value: attributes.link, onChange: function(val) { setAttributes({ link: val }); } }),
                el(MediaSelect, { id: attributes.image, url: attributes.image, onSelect: function(media) { setAttributes({ image: media.url }); } }),
                el('div', { style: { display: 'flex', gap: '10px' } },
                    el('div', { style: { flex: 1 } },
                        el(TextControl, { label: 'KPI 1 Label', value: attributes.kpi1Label, onChange: function(val) { setAttributes({ kpi1Label: val }); } }),
                        el(TextControl, { label: 'KPI 1 Value', value: attributes.kpi1Value, onChange: function(val) { setAttributes({ kpi1Value: val }); } })
                    ),
                    el('div', { style: { flex: 1 } },
                        el(TextControl, { label: 'KPI 2 Label', value: attributes.kpi2Label, onChange: function(val) { setAttributes({ kpi2Label: val }); } }),
                        el(TextControl, { label: 'KPI 2 Value', value: attributes.kpi2Value, onChange: function(val) { setAttributes({ kpi2Value: val }); } })
                    ),
                    el('div', { style: { flex: 1 } },
                        el(TextControl, { label: 'KPI 3 Label', value: attributes.kpi3Label, onChange: function(val) { setAttributes({ kpi3Label: val }); } }),
                        el(TextControl, { label: 'KPI 3 Value', value: attributes.kpi3Value, onChange: function(val) { setAttributes({ kpi3Value: val }); } })
                    )
                )
            );
        },
        save: function(props) {
            var attrs = props.attributes;
            return el('div', { className: 'client-slide', 'data-client': attrs.clientName, 'data-kpi1-label': attrs.kpi1Label, 'data-kpi1-value': attrs.kpi1Value, 'data-kpi2-label': attrs.kpi2Label, 'data-kpi2-value': attrs.kpi2Value, 'data-kpi3-label': attrs.kpi3Label, 'data-kpi3-value': attrs.kpi3Value, 'data-link': attrs.link, 'data-image': attrs.image });
        }
    });

    // 31. Clients List Block (with options)
    blocks.registerBlockType('e3es/clients-list', {
        title: 'Clients List Directory',
        icon: 'list-view',
        category: 'layout',
        attributes: {
            defaultRegion: { type: 'string', default: 'All' }
        },
        edit: function(props) {
            return el('div', { style: { background: '#f0fdf4', padding: '20px', border: '1px solid #16a34a', borderRadius: '8px' } },
                el('h3', { style: { margin: 0, color: '#16a34a' } }, 'Clients List Directory'),
                el('p', null, 'This block will render the dynamic Clients List on the frontend.'),
                el(SelectControl, {
                    label: 'Default Filter Region',
                    value: props.attributes.defaultRegion,
                    options: [
                        { label: 'All Regions', value: 'All' },
                        { label: 'Panhandle (ESC 16, 17)', value: 'panhandle' },
                        { label: 'West (ESC 18, 19)', value: 'west' },
                        { label: 'North (ESC 9, 10, 11)', value: 'north' },
                        { label: 'Northeast (ESC 8)', value: 'northeast' },
                        { label: 'Southeast (ESC 5)', value: 'southeast' },
                        { label: 'Central (ESC 6, 7, 12, 13)', value: 'central' },
                        { label: 'Hill Country (ESC 14, 15)', value: 'hill-country' },
                        { label: 'South (ESC 1, 2, 3, 4, 20)', value: 'south' }
                    ],
                    onChange: function(val) { props.setAttributes({ defaultRegion: val }); }
                })
            );
        },
        save: function(props) {
            return el('div', { className: 'clients-list-placeholder', 'data-default-region': props.attributes.defaultRegion });
        }
    });

    // 32. Project History Interactive Map
    blocks.registerBlockType('e3es/project-history', {
        title: 'Project History Map & Spreadsheet',
        icon: 'location',
        category: 'layout',
        edit: function(props) {
            return el('div', { style: { background: '#f8fafc', padding: '20px', border: '1px solid #94a3b8', borderRadius: '8px' } },
                el('h3', { style: { margin: 0, color: '#334155' } }, 'Project History (Interactive Map)'),
                el('p', null, 'This block will render the 10-Year Project History interactive map and spreadsheet view on the frontend.')
            );
        },
        save: function() {
            return el('div', { className: 'project-history-placeholder' });
        }
    });

    // 33. E3 Sales Rep by Region
    blocks.registerBlockType('e3es/sales-rep-region', {
        title: 'E3 Sales Rep by Region',
        icon: 'admin-users',
        category: 'layout',
        edit: function(props) {
            return el('div', { style: { background: '#f8fafc', padding: '20px', border: '1px solid #94a3b8', borderRadius: '8px' } },
                el('h3', { style: { margin: 0, color: '#334155' } }, 'E3 Sales Rep by Region Map'),
                el('p', null, 'This block renders the interactive map that displays the sales representative on hover.')
            );
        },
        save: function() {
            return el('e3-sales-rep-selector');
        }
    });

    // Always expand the list view, page options, and never enable fullscreen mode by default
    window.wp.domReady(function() {
        var attempts = 0;
        var checkEditorInterval = setInterval(function() {
            attempts++;
            try {
                if (
                    window.wp &&
                    window.wp.data &&
                    window.wp.data.select('core/edit-post') &&
                    window.wp.data.select('core/editor') &&
                    window.wp.data.select('core/editor').getCurrentPostType()
                ) {
                    clearInterval(checkEditorInterval);
                    
                    var select = window.wp.data.select('core/edit-post');
                    var dispatch = window.wp.data.dispatch('core/edit-post');

                    if (select && dispatch) {
                        // 1. Never enable fullscreen mode by default
                        if (typeof select.isFeatureActive === 'function' && select.isFeatureActive('fullscreenMode')) {
                            dispatch.toggleFeature('fullscreenMode');
                        }

                        // 2. Always expand the list view by default
                        if (typeof select.isListViewOpened === 'function' && !select.isListViewOpened()) {
                            dispatch.setIsListViewOpened(true);
                        }

                        // 3. Always expand the page options (settings sidebar) by default
                        if (typeof select.isEditorSidebarOpened === 'function' && !select.isEditorSidebarOpened()) {
                            dispatch.openGeneralSidebar('edit-post/document');
                        }
                    }
                }
            } catch (err) {
                // Silently catch errors if block editor store isn't fully ready
            }
            if (attempts > 50) {
                clearInterval(checkEditorInterval);
            }
        }, 100);
    });

})(window.wp.blocks, window.wp.blockEditor, window.wp.element, window.wp.components);


