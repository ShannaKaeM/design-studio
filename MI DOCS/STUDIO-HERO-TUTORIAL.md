# Studio Hero Component Tutorial

## Method 1: Manual Block Creation (Simplest)

### Step 1: Add GenerateBlocks in Editor
1. Open the page/post editor
2. Add a **GenerateBlocks Container** block
3. In the block settings, add class: `studio-hero-section`

### Step 2: Add Inner Blocks
1. Inside the container, add another **GenerateBlocks Container**
2. Add class: `studio-hero-content`
3. Inside that, add:
   - **GenerateBlocks Headline** with class: `studio-hero-title`
   - **GenerateBlocks Headline** (set to p tag) with class: `studio-hero-description`

### Block Structure:
```
Container (.studio-hero-section)
  └── Container (.studio-hero-content)
      ├── Headline h2 (.studio-hero-title)
      └── Headline p (.studio-hero-description)
```

## Method 2: Using the Selector Builder

### Understanding the Selector Builder
The Selector Builder lets you apply CSS variables to ANY element without creating classes.

### Step 1: Access Studio System
1. Go to **Appearance > Studio System**
2. Click on **Selectors** tab

### Step 2: Create Hero Selectors
Instead of classes, you can target GenerateBlocks directly:

**Selector 1: Hero Container**
- Name: `hero-container`
- Selector: `.gb-container.hero-section`
- Variables:
  ```css
  margin: var(--st-hero-margin)
  padding: var(--st-hero-padding)
  background-color: var(--st-hero-bg)
  border-radius: var(--st-hero-radius)
  min-height: var(--st-hero-min-height)
  ```

**Selector 2: Hero Title**
- Name: `hero-title`
- Selector: `.hero-section h2`
- Variables:
  ```css
  font-size: var(--st-hero-title-size)
  color: var(--st-hero-title-color)
  ```

## Method 3: Custom HTML Elements (Advanced)

### Write Semantic HTML:
```html
<hero background="#f0f4f8" radius="xl" spacing="spacious">
  <hero-title>Your Amazing Title</hero-title>
  <hero-content>
    <p>Your description text goes here. It can be multiple lines.</p>
  </hero-content>
</hero>
```

This automatically converts to GenerateBlocks!

## Using the Studio Admin Interface

### Variables Tab
- All your CSS variables with controls
- Change `--st-hero-bg` color with color picker
- Adjust `--st-hero-margin` with slider
- Changes apply instantly site-wide

### Selectors Tab
- Create rules that apply variables to elements
- No need to write CSS
- Target by class, ID, or element type

### CSS Sync Tab
- See all your CSS classes
- Edit properties directly
- Bundle and optimize output

## Naming Convention Suggestions

### For Manual Classes:
```
.studio-hero-section      (main container)
.studio-hero-content      (content wrapper)
.studio-hero-title        (h1/h2 title)
.studio-hero-description  (paragraph text)
.studio-hero-cta          (call-to-action buttons)
```

### For GenerateBlocks Custom Classes:
```
hero-section
hero-content
hero-title
hero-desc
```

### For Semantic Naming (AI-Friendly):
```
<hero>
<hero-title>
<hero-content>
<hero-cta>
```

## Quick Start Test

1. **Create a test page**
2. **Add this block structure:**
   ```
   GenerateBlocks Container
   - Additional CSS Classes: studio-hero-section
   - No other settings needed!
   
     └── GenerateBlocks Container
         - Additional CSS Classes: studio-hero-content
         
         └── GenerateBlocks Headline (h2)
             - Additional CSS Classes: studio-hero-title
             - Text: "Welcome to Our Site"
             
         └── GenerateBlocks Headline (p)
             - Additional CSS Classes: studio-hero-description
             - Text: "This is a beautiful hero section"
   ```

3. **Save and preview** - Your styled hero should appear!

4. **Go to Studio System** and adjust the variables to see live changes

## Pro Tips

1. **Start Simple**: Use manual classes first to understand the system
2. **Use Variables**: All styling through CSS variables, not inline styles
3. **Consistent Naming**: Pick a naming pattern and stick with it
4. **Test Changes**: Use the Studio admin to test variable changes live

## Next Steps

Once comfortable with manual approach:
1. Try the Selector Builder for dynamic styling
2. Experiment with Custom HTML elements
3. Create reusable patterns
4. Train AI on your patterns

This approach gives you full control while building toward automation!