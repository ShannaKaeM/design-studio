{
    "version": 2,
    "settings": {
        "appearanceTools": true,
        "useRootPaddingAwareAlignments": true,
        "color": {
            "custom": true,
            "customDuotone": true,
            "customGradient": true,
            "defaultDuotone": false,
            "defaultGradients": false,
            "defaultPalette": false,
            "duotone": [
                {
                    "colors": [
                        "#000000",
                        "#ffffff"
                    ],
                    "slug": "foreground-and-background",
                    "name": "Foreground and background"
                },
                {
                    "colors": [
                        "#000000",
                        "#abb8c3"
                    ],
                    "slug": "foreground-and-secondary",
                    "name": "Foreground and secondary"
                }
            ],
            "gradients": [
                {
                    "slug": "vivid-cyan-blue-to-vivid-purple",
                    "gradient": "linear-gradient(135deg,rgba(6,147,227,1) 0%,rgb(155,81,224) 100%)",
                    "name": "Vivid cyan blue to vivid purple"
                }
            ],
            "palette": [
                {
                    "name": "Primary",
                    "slug": "primary",
                    "color": "#5a7f80"
                },
                {
                    "name": "Blocksy Secondary",
                    "slug": "blocksy-secondary",
                    "color": "#4b8daa"
                },
                {
                    "name": "Blocksy Accent",
                    "slug": "blocksy-accent",
                    "color": "#3A4F66"
                },
                {
                    "name": "Blocksy Dark",
                    "slug": "blocksy-dark",
                    "color": "#192a3d"
                },
                {
                    "name": "Blocksy Light",
                    "slug": "blocksy-light",
                    "color": "#dedede"
                },
                {
                    "name": "Primary Content",
                    "slug": "blocksy-primary-content",
                    "color": "#ffffff"
                }
            ]
        },
        "layout": {
            "contentSize": "var(--theme-block-max-width, 620px)",
            "wideSize": "var(--theme-block-wide-max-width, 1280px)",
            "fullSize": "none"
        },
        "spacing": {
            "blockGap": true,
            "margin": true,
            "padding": true,
            "units": [
                "%",
                "px",
                "em",
                "rem",
                "vh",
                "vw"
            ],
            "spacingSizes": [
                {
                    "size": "0.5rem",
                    "slug": "30",
                    "name": "1"
                },
                {
                    "size": "1rem",
                    "slug": "40",
                    "name": "2"
                },
                {
                    "size": "1.5rem",
                    "slug": "50",
                    "name": "3"
                },
                {
                    "size": "2rem",
                    "slug": "60",
                    "name": "4"
                },
                {
                    "size": "2.5rem",
                    "slug": "70",
                    "name": "5"
                },
                {
                    "size": "3rem",
                    "slug": "80",
                    "name": "6"
                },
                {
                    "size": "4rem",
                    "slug": "90",
                    "name": "7"
                },
                {
                    "size": "5rem",
                    "slug": "100",
                    "name": "8"
                }
            ]
        },
        "typography": {
            "customFontSize": true,
            "dropCap": true,
            "fluid": true,
            "fontStyle": true,
            "fontWeight": true,
            "letterSpacing": true,
            "lineHeight": true,
            "textDecoration": true,
            "textTransform": true,
            "fontFamilies": [
                {
                    "fontFamily": "-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen-Sans, Ubuntu, Cantarell, 'Helvetica Neue', sans-serif",
                    "slug": "system-font",
                    "name": "System Font"
                },
                {
                    "fontFamily": "Georgia, serif",
                    "slug": "serif",
                    "name": "Serif"
                },
                {
                    "fontFamily": "'Courier New', Courier, monospace",
                    "slug": "monospace",
                    "name": "Monospace"
                }
            ],
            "fontSizes": [
                {
                    "fluid": false,
                    "size": "13px",
                    "slug": "small",
                    "name": "Small"
                },
                {
                    "fluid": false,
                    "size": "16px",
                    "slug": "base",
                    "name": "Base"
                },
                {
                    "fluid": false,
                    "size": "20px",
                    "slug": "medium",
                    "name": "Medium"
                },
                {
                    "fluid": {
                        "min": "22px",
                        "max": "30px"
                    },
                    "size": "30px",
                    "slug": "large",
                    "name": "Large"
                },
                {
                    "fluid": {
                        "min": "30px",
                        "max": "42px"
                    },
                    "size": "42px",
                    "slug": "x-large",
                    "name": "Extra Large"
                },
                {
                    "fluid": {
                        "min": "45px",
                        "max": "80px"
                    },
                    "size": "80px",
                    "slug": "xx-large",
                    "name": "Extra Extra Large"
                }
            ]
        },
        "border": {
            "color": true,
            "radius": true,
            "style": true,
            "width": true
        },
        "shadow": {
            "defaultPresets": true,
            "presets": [
                {
                    "name": "Natural",
                    "slug": "natural",
                    "shadow": "6px 6px 9px rgba(0, 0, 0, 0.2)"
                },
                {
                    "name": "Deep",
                    "slug": "deep",
                    "shadow": "12px 12px 50px rgba(0, 0, 0, 0.4)"
                },
                {
                    "name": "Sharp",
                    "slug": "sharp",
                    "shadow": "6px 6px 0px rgba(0, 0, 0, 0.2)"
                },
                {
                    "name": "Outlined",
                    "slug": "outlined",
                    "shadow": "6px 6px 0px -3px rgba(255, 255, 255, 1), 6px 6px rgba(0, 0, 0, 1)"
                },
                {
                    "name": "Crisp",
                    "slug": "crisp",
                    "shadow": "6px 6px 0px rgba(0, 0, 0, 1)"
                }
            ]
        },
        "dimensions": {
            "aspectRatio": true,
            "minHeight": true
        },
        "position": {
            "sticky": true
        },
        "blocks": {
            "core/navigation": {
                "spacing": {
                    "blockGap": true
                }
            },
            "core/pullquote": {
                "border": {
                    "color": true,
                    "radius": true,
                    "style": true,
                    "width": true
                }
            },
            "core/query": {
                "spacing": {
                    "blockGap": true
                }
            },
            "core/query-pagination": {
                "spacing": {
                    "blockGap": true
                }
            },
            "core/query-pagination-next": {
                "spacing": {
                    "blockGap": true
                }
            },
            "core/query-pagination-numbers": {
                "spacing": {
                    "blockGap": true
                }
            },
            "core/query-pagination-previous": {
                "spacing": {
                    "blockGap": true
                }
            },
            "core/social-links": {
                "spacing": {
                    "blockGap": {
                        "horizontal": "0.5rem",
                        "vertical": "0.5rem"
                    }
                }
            }
        },
        "custom": {
            "blocksyIntegration": {
                "version": "1.0.0",
                "maxSiteWidth": "var(--theme-block-max-width, 1290px)",
                "contentAreaSpacing": "var(--theme-content-spacing, 60px)",
                "colorPalette": {
                    "color1": "#2872fa",
                    "color2": "#1559ed",
                    "color3": "#3A4F66",
                    "color4": "#192a3d",
                    "color5": "#ffffff"
                },
                "typography": {
                    "rootFont": "System Default",
                    "headingFont": "System Default",
                    "fluidTypography": true
                }
            }
        }
    },
    "styles": {
        "color": {
            "background": "var(--wp--preset--color--white)",
            "text": "var(--wp--preset--color--black)"
        },
        "typography": {
            "fontFamily": "var(--wp--preset--font-family--system-font)",
            "fontSize": "var(--wp--preset--font-size--medium)",
            "lineHeight": "1.6"
        },
        "spacing": {
            "blockGap": "var(--theme-content-spacing, 1.5rem)"
        },
        "elements": {
            "link": {
                "color": {
                    "text": "var(--wp--preset--color--color1)"
                },
                "typography": {
                    "textDecoration": "none"
                },
                ":hover": {
                    "color": {
                        "text": "var(--wp--preset--color--color2)"
                    },
                    "typography": {
                        "textDecoration": "underline"
                    }
                },
                ":focus": {
                    "color": {
                        "text": "var(--wp--preset--color--color2)"
                    },
                    "typography": {
                        "textDecoration": "underline dashed"
                    }
                }
            },
            "h1": {
                "typography": {
                    "fontSize": "var(--wp--preset--font-size--xx-large)",
                    "lineHeight": "1.2",
                    "fontWeight": "700"
                }
            },
            "h2": {
                "typography": {
                    "fontSize": "var(--wp--preset--font-size--x-large)",
                    "lineHeight": "1.3",
                    "fontWeight": "700"
                }
            },
            "h3": {
                "typography": {
                    "fontSize": "var(--wp--preset--font-size--large)",
                    "lineHeight": "1.4",
                    "fontWeight": "700"
                }
            },
            "h4": {
                "typography": {
                    "fontSize": "var(--wp--preset--font-size--medium)",
                    "lineHeight": "1.5",
                    "fontWeight": "700"
                }
            },
            "h5": {
                "typography": {
                    "fontSize": "var(--wp--preset--font-size--medium)",
                    "lineHeight": "1.6",
                    "fontWeight": "600"
                }
            },
            "h6": {
                "typography": {
                    "fontSize": "var(--wp--preset--font-size--base)",
                    "lineHeight": "1.6",
                    "fontWeight": "600"
                }
            },
            "button": {
                "border": {
                    "radius": "4px"
                },
                "color": {
                    "background": "var(--wp--preset--color--color1)",
                    "text": "var(--wp--preset--color--white)"
                },
                "spacing": {
                    "padding": {
                        "top": "0.75rem",
                        "right": "1.5rem",
                        "bottom": "0.75rem",
                        "left": "1.5rem"
                    }
                },
                "typography": {
                    "fontSize": "var(--wp--preset--font-size--base)",
                    "fontWeight": "500"
                },
                ":hover": {
                    "color": {
                        "background": "var(--wp--preset--color--color2)"
                    }
                }
            }
        },
        "blocks": {
            "core/navigation": {
                "elements": {
                    "link": {
                        "typography": {
                            "textDecoration": "none"
                        },
                        ":hover": {
                            "typography": {
                                "textDecoration": "underline"
                            }
                        },
                        ":focus": {
                            "typography": {
                                "textDecoration": "underline dashed"
                            }
                        },
                        ":active": {
                            "typography": {
                                "textDecoration": "none"
                            }
                        }
                    }
                }
            },
            "core/post-title": {
                "elements": {
                    "link": {
                        "typography": {
                            "textDecoration": "none"
                        },
                        ":hover": {
                            "typography": {
                                "textDecoration": "underline"
                            }
                        },
                        ":focus": {
                            "typography": {
                                "textDecoration": "underline dashed"
                            }
                        },
                        ":active": {
                            "typography": {
                                "textDecoration": "none"
                            }
                        }
                    }
                }
            },
            "core/pullquote": {
                "border": {
                    "color": "var(--wp--preset--color--cyan-bluish-gray)",
                    "style": "solid",
                    "width": "1px 0"
                },
                "spacing": {
                    "padding": {
                        "left": "1rem",
                        "right": "1rem",
                        "top": "1rem",
                        "bottom": "1rem"
                    }
                },
                "typography": {
                    "fontSize": "var(--wp--preset--font-size--large)",
                    "fontStyle": "italic"
                }
            },
            "core/query": {
                "elements": {
                    "h2": {
                        "typography": {
                            "fontSize": "var(--wp--preset--font-size--large)"
                        }
                    }
                }
            },
            "core/query-pagination": {
                "typography": {
                    "fontSize": "var(--wp--preset--font-size--small)",
                    "fontWeight": "400"
                },
                "elements": {
                    "link": {
                        "typography": {
                            "textDecoration": "none"
                        },
                        ":hover": {
                            "typography": {
                                "textDecoration": "underline"
                            }
                        }
                    }
                }
            },
            "core/quote": {
                "border": {
                    "color": "var(--wp--preset--color--color1)",
                    "style": "solid",
                    "width": "0 0 0 4px"
                },
                "spacing": {
                    "padding": {
                        "left": "1rem"
                    }
                },
                "typography": {
                    "fontStyle": "italic"
                }
            },
            "core/search": {
                "typography": {
                    "fontSize": "var(--wp--preset--font-size--medium)",
                    "lineHeight": "1.6"
                },
                "border": {
                    "radius": "4px"
                }
            },
            "core/separator": {
                "border": {
                    "color": "currentColor",
                    "style": "solid",
                    "width": "0 0 1px 0"
                },
                "color": {
                    "text": "var(--wp--preset--color--cyan-bluish-gray)"
                }
            },
            "core/site-tagline": {
                "typography": {
                    "fontSize": "var(--wp--preset--font-size--small)"
                }
            },
            "core/site-title": {
                "elements": {
                    "link": {
                        "typography": {
                            "textDecoration": "none"
                        },
                        ":hover": {
                            "typography": {
                                "textDecoration": "underline"
                            }
                        },
                        ":focus": {
                            "typography": {
                                "textDecoration": "underline dashed"
                            }
                        },
                        ":active": {
                            "typography": {
                                "textDecoration": "none"
                            }
                        }
                    }
                },
                "typography": {
                    "fontSize": "var(--wp--preset--font-size--large)",
                    "fontWeight": "600",
                    "lineHeight": "1.4"
                }
            }
        }
    },
    "templateParts": [
        {
            "name": "header",
            "title": "Header",
            "area": "header"
        },
        {
            "name": "footer",
            "title": "Footer",
            "area": "footer"
        }
    ]
}