# Original Clean Theme.json (from suredash branch)

This is the original clean `theme.json` structure from before the plugin-to-theme conversion:

```json
{
    "version": 2,
    "settings": {
        "color": {
            "custom": true,
            "customDuotone": true,
            "customGradient": true,
            "defaultDuotone": false,
            "defaultGradients": false,
            "defaultPalette": false,
            "palette": [
                {
                    "name": "Primary Light",
                    "slug": "primary-light",
                    "color": "#d6dcd6"
                },
                {
                    "name": "Primary",
                    "slug": "primary",
                    "color": "#5a7b7c"
                },
                {
                    "name": "Primary Dark",
                    "slug": "primary-dark",
                    "color": "#3a5a59"
                },
                {
                    "name": "Secondary Light",
                    "slug": "secondary-light",
                    "color": "#2c2c2c"
                },
                {
                    "name": "Secondary",
                    "slug": "secondary",
                    "color": "#975d55"
                },
                {
                    "name": "Secondary Dark",
                    "slug": "secondary-dark",
                    "color": "#853d2d"
                },
                {
                    "name": "Neutral Light",
                    "slug": "neutral-light",
                    "color": "#d8d6cf"
                },
                {
                    "name": "Neutral",
                    "slug": "neutral",
                    "color": "#b3b09f"
                },
                {
                    "name": "Neutral Dark",
                    "slug": "neutral-dark",
                    "color": "#8e897b"
                },
                {
                    "name": "Base Lightest",
                    "slug": "base-lightest",
                    "color": "#ffffff"
                },
                {
                    "name": "Base Lighter",
                    "slug": "base-lighter",
                    "color": "#f0f0f0"
                },
                {
                    "name": "Base Light",
                    "slug": "base-light",
                    "color": "#cacaca"
                },
                {
                    "name": "Base",
                    "slug": "base",
                    "color": "#777777"
                },
                {
                    "name": "Base Dark",
                    "slug": "base-dark",
                    "color": "#606060"
                },
                {
                    "name": "Base Darker",
                    "slug": "base-darker",
                    "color": "#404040"
                },
                {
                    "name": "Base Darkest",
                    "slug": "base-darkest",
                    "color": "#000000"
                },
                {
                    "name": "Success",
                    "slug": "success",
                    "color": "#10b981"
                },
                {
                    "name": "Warning",
                    "slug": "warning",
                    "color": "#f59e0b"
                },
                {
                    "name": "Error",
                    "slug": "error",
                    "color": "#ef4444"
                },
                {
                    "name": "Info",
                    "slug": "info",
                    "color": "#3b82f6"
                }
            ],
            "gradients": [
                {
                    "name": "Primary Gradient",
                    "slug": "primary",
                    "gradient": "linear-gradient(90deg, #d6dcd6 0%, #3a5a59 100%)"
                },
                {
                    "name": "Secondary Gradient",
                    "slug": "secondary",
                    "gradient": "linear-gradient(90deg, #2c2c2c 0%, #853d2d 100%)"
                },
                {
                    "name": "Neutral Gradient",
                    "slug": "neutral",
                    "gradient": "linear-gradient(90deg, #d8d6cf 0%, #8e897b 100%)"
                },
                {
                    "name": "Base Gradient",
                    "slug": "base",
                    "gradient": "linear-gradient(90deg, #ffffff 0%, #000000 100%)"
                }
            ]
        },
        "layout": {
            "contentWidth": "1200px",
            "wideWidth": "1400px",
            "fullWidth": "100vw",
            "useRootPaddingAwareAlignments": false,
            "appearanceTools": true,
            "blockGap": true,
            "margin": true,
            "padding": true,
            "rootPadding": {
                "top": "0px",
                "right": "0px",
                "bottom": "0px",
                "left": "0px"
            }
        },
        "useRootPaddingAwareAlignments": false,
        "appearanceTools": true,
        "spacing": {
            "blockGap": true,
            "margin": true,
            "padding": true,
            "spacingScale": [
                {
                    "size": "2px",
                    "slug": "xxs",
                    "name": "Xxs"
                },
                {
                    "size": "4px",
                    "slug": "xs",
                    "name": "Xs"
                },
                {
                    "size": "8px",
                    "slug": "sm",
                    "name": "Sm"
                },
                {
                    "size": "16px",
                    "slug": "md",
                    "name": "Md"
                },
                {
                    "size": "24px",
                    "slug": "lg",
                    "name": "Lg"
                },
                {
                    "size": "32px",
                    "slug": "xl",
                    "name": "Xl"
                },
                {
                    "size": "40px",
                    "slug": "xxl",
                    "name": "Xxl"
                },
                {
                    "size": "48px",
                    "slug": "xxxl",
                    "name": "Xxxl"
                }
            ],
            "customSpacingSize": true,
            "spacingSizes": [
                {
                    "size": "2px",
                    "slug": "xxs",
                    "name": "Xxs"
                },
                {
                    "size": "4px",
                    "slug": "xs",
                    "name": "Xs"
                },
                {
                    "size": "8px",
                    "slug": "sm",
                    "name": "Sm"
                },
                {
                    "size": "16px",
                    "slug": "md",
                    "name": "Md"
                },
                {
                    "size": "24px",
                    "slug": "lg",
                    "name": "Lg"
                },
                {
                    "size": "32px",
                    "slug": "xl",
                    "name": "Xl"
                },
                {
                    "size": "40px",
                    "slug": "xxl",
                    "name": "Xxl"
                },
                {
                    "size": "48px",
                    "slug": "xxxl",
                    "name": "Xxxl"
                }
            ],
            "units": [
                "px",
                "em",
                "rem",
                "vh",
                "vw",
                "%"
            ]
        },
        "custom": {
            "designTokens": {
                "version": "2.0.0",
                "lastUpdated": "2025-06-14 05:38:38",
                "categories": {
                    "theme": {
                        "name": "Theme Colors",
                        "icon": "",
                        "order": 1
                    },
                    "notifications": {
                        "name": "Notification Colors",
                        "icon": "🔔",
                        "order": 2
                    },
                    "gradients": {
                        "name": "Gradients",
                        "icon": "🌈",
                        "order": 3
                    },
                    "layout": {
                        "name": "Layout",
                        "icon": "🌐",
                        "order": 4
                    },
                    "spacing": {
                        "name": "Spacing",
                        "icon": "💏",
                        "order": 5
                    },
                    "typography": {
                        "name": "Typography",
                        "icon": "📝",
                        "order": 6
                    }
                },
                "colors": {
                    "primary-light": {
                        "value": "#d6dcd6",
                        "name": "Primary Light",
                        "category": "theme",
                        "order": 1
                    },
                    "primary": {
                        "value": "#5a7b7c",
                        "name": "Primary",
                        "category": "theme",
                        "order": 2
                    },
                    "primary-dark": {
                        "value": "#3a5a59",
                        "name": "Primary Dark",
                        "category": "theme",
                        "order": 3
                    },
                    "secondary-light": {
                        "value": "#2c2c2c",
                        "name": "Secondary Light",
                        "category": "theme",
                        "order": 4
                    },
                    "secondary": {
                        "value": "#975d55",
                        "name": "Secondary",
                        "category": "theme",
                        "order": 5
                    },
                    "secondary-dark": {
                        "value": "#853d2d",
                        "name": "Secondary Dark",
                        "category": "theme",
                        "order": 6
                    },
                    "neutral-light": {
                        "value": "#d8d6cf",
                        "name": "Neutral Light",
                        "category": "theme",
                        "order": 7
                    },
                    "neutral": {
                        "value": "#b3b09f",
                        "name": "Neutral",
                        "category": "theme",
                        "order": 8
                    },
                    "neutral-dark": {
                        "value": "#8e897b",
                        "name": "Neutral Dark",
                        "category": "theme",
                        "order": 9
                    },
                    "base-lightest": {
                        "value": "#ffffff",
                        "name": "Base Lightest",
                        "category": "theme",
                        "order": 10
                    },
                    "base-lighter": {
                        "value": "#f0f0f0",
                        "name": "Base Lighter",
                        "category": "theme",
                        "order": 11
                    },
                    "base-light": {
                        "value": "#cacaca",
                        "name": "Base Light",
                        "category": "theme",
                        "order": 12
                    },
                    "base": {
                        "value": "#777777",
                        "name": "Base",
                        "category": "theme",
                        "order": 13
                    },
                    "base-dark": {
                        "value": "#606060",
                        "name": "Base Dark",
                        "category": "theme",
                        "order": 14
                    },
                    "base-darker": {
                        "value": "#404040",
                        "name": "Base Darker",
                        "category": "theme",
                        "order": 15
                    },
                    "base-darkest": {
                        "value": "#000000",
                        "name": "Base Darkest",
                        "category": "theme",
                        "order": 16
                    },
                    "success": {
                        "value": "#10b981",
                        "name": "Success",
                        "category": "notifications",
                        "order": 1
                    },
                    "warning": {
                        "value": "#f59e0b",
                        "name": "Warning",
                        "category": "notifications",
                        "order": 2
                    },
                    "error": {
                        "value": "#ef4444",
                        "name": "Error",
                        "category": "notifications",
                        "order": 3
                    },
                    "info": {
                        "value": "#3b82f6",
                        "name": "Info",
                        "category": "notifications",
                        "order": 4
                    }
                },
                "gradients": {
                    "primary": {
                        "value": "linear-gradient(90deg, #d6dcd6 0%, #3a5a59 100%)",
                        "name": "Primary Gradient",
                        "category": "gradients",
                        "order": 1
                    },
                    "secondary": {
                        "value": "linear-gradient(90deg, #2c2c2c 0%, #853d2d 100%)",
                        "name": "Secondary Gradient",
                        "category": "gradients",
                        "order": 2
                    },
                    "neutral": {
                        "value": "linear-gradient(90deg, #d8d6cf 0%, #8e897b 100%)",
                        "name": "Neutral Gradient",
                        "category": "gradients",
                        "order": 3
                    },
                    "base": {
                        "value": "linear-gradient(90deg, #ffffff 0%, #000000 100%)",
                        "name": "Base Gradient",
                        "category": "gradients",
                        "order": 4
                    }
                },
                "layout": {
                    "contentWidth": "1200px",
                    "wideWidth": "1400px",
                    "fullWidth": "100vw"
                },
                "spacing": {
                    "xxs": "2px",
                    "xs": "4px",
                    "sm": "8px",
                    "md": "16px",
                    "lg": "24px",
                    "xl": "32px",
                    "xxl": "40px",
                    "xxxl": "48px"
                },
                "typography": {
                    "fontFamilies": {
                        "primary": {
                            "name": "Montserrat",
                            "value": "Montserrat, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif",
                            "category": "primary"
                        },
                        "secondary": {
                            "name": "Inter",
                            "value": "Inter, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif",
                            "category": "secondary"
                        }
                    },
                    "fontSizes": {
                        "xs": "12px",
                        "sm": "14px",
                        "md": "16px",
                        "lg": "18px",
                        "xl": "20px",
                        "xxl": "24px",
                        "xxxl": "48px"
                    },
                    "lineHeights": {
                        "xs": "16px",
                        "sm": "20px",
                        "md": "24px",
                        "lg": "28px",
                        "xl": "32px",
                        "xxl": "36px",
                        "xxxl": "40px"
                    },
                    "fontWeights": {
                        "light": 300,
                        "regular": 400,
                        "medium": 500,
                        "semibold": 600,
                        "bold": 700
                    }
                }
            }
        }
    }
}
```

## Key Differences

**Your Original Structure:**
- ✅ Clean color palette with your **neutral** colors instead of accent
- ✅ Proper gradient structure in both main palette and custom designTokens
- ✅ Layout settings properly positioned at root level
- ✅ Spacing with both spacingScale and spacingSizes
- ✅ Complete design tokens in custom section with categories, order, etc.

**Current Problematic Structure:**
- ❌ Duplicate color definitions (both original and new accent colors)
- ❌ Colors buried in custom section instead of main palette
- ❌ Missing proper gradient integration
- ❌ Inconsistent spacing structure

The original structure is much cleaner and properly organized. Would you like me to restore the theme.json to this original structure?
