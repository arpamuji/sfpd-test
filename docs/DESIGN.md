# Design System - shadcn/ui (Tailwind CSS v4)

> **Application Context**: Internal enterprise tool for warehouse distribution approval workflow. Not a SaaS product — designed for efficiency, clarity, and daily use by operations staff and management.

---

## 1. Overview

**Design Philosophy**: Clean, minimal, and functional. shadcn/ui provides copy-paste components that you own and customize. Built on Radix UI primitives for accessibility and Tailwind CSS v4 for styling.

### Internal Tools Priorities
1. **Information Density**: Staff use this 8+ hours/day
2. **Action Clarity**: Primary actions instantly identifiable
3. **Status Visibility**: Approval state obvious at a glance
4. **Keyboard Efficiency**: Minimize mouse dependency
5. **Reduced Eye Strain**: Proper contrast and spacing

---

## 2. Color System (Tailwind v4 + OKLCH)

### CSS Variables (app/globals.css)

```css
@import "tailwindcss";
@import "shadcn/tailwind.css";

@custom-variant dark (&:is(.dark *));

@theme inline {
  --color-background: var(--background);
  --color-foreground: var(--foreground);
  --color-card: var(--card);
  --color-card-foreground: var(--card-foreground);
  --color-primary: var(--primary);
  --color-primary-foreground: var(--primary-foreground);
  --color-secondary: var(--secondary);
  --color-secondary-foreground: var(--secondary-foreground);
  --color-muted: var(--muted);
  --color-muted-foreground: var(--muted-foreground);
  --color-accent: var(--accent);
  --color-accent-foreground: var(--accent-foreground);
  --color-destructive: var(--destructive);
  --color-destructive-foreground: var(--destructive-foreground);
  --color-border: var(--border);
  --color-input: var(--input);
  --color-ring: var(--ring);
}

:root {
  --radius: 0.625rem;
  --background: oklch(1 0 0);
  --foreground: oklch(0.145 0 0);
  --card: oklch(1 0 0);
  --card-foreground: oklch(0.145 0 0);
  --primary: oklch(0.205 0 0);
  --primary-foreground: oklch(0.985 0 0);
  --secondary: oklch(0.97 0 0);
  --secondary-foreground: oklch(0.205 0 0);
  --muted: oklch(0.97 0 0);
  --muted-foreground: oklch(0.556 0 0);
  --accent: oklch(0.97 0 0);
  --accent-foreground: oklch(0.205 0 0);
  --destructive: oklch(0.577 0.245 27.325);
  --destructive-foreground: oklch(0.985 0 0);
  --border: oklch(0.922 0 0);
  --input: oklch(0.922 0 0);
  --ring: oklch(0.708 0 0);
}
```

### Semantic Color Usage

| Token | Light Mode | Usage |
|-------|------------|-------|
| `background` | `oklch(1 0 0)` | Page background (white) |
| `foreground` | `oklch(0.145 0 0)` | Primary text (near black) |
| `card` | `oklch(1 0 0)` | Card backgrounds |
| `primary` | `oklch(0.205 0 0)` | Primary buttons, active states |
| `secondary` | `oklch(0.97 0 0)` | Secondary buttons, subtle backgrounds |
| `muted` | `oklch(0.97 0 0)` | Disabled states, subtle elements |
| `muted-foreground` | `oklch(0.556 0 0)` | Secondary text |
| `destructive` | `oklch(0.577 0.245 27.325)` | Error states, reject actions |
| `border` | `oklch(0.922 0 0)` | Input borders, dividers |

### Status Colors (Application-Specific)

| Status | Background | Text | Usage |
|--------|------------|------|-------|
| Approved | `oklch(0.96 0.04 150)` | `oklch(0.35 0.08 150)` | Success badges |
| Pending | `oklch(0.95 0.05 85)` | `oklch(0.45 0.1 85)` | Pending badges |
| Rejected | `oklch(0.9 0.08 25)` | `oklch(0.5 0.15 25)` | Error/reject badges |

---

## 3. Typography

**Font**: Inter (default shadcn font)

| Element | Size | Weight | Line Height |
|---------|------|--------|-------------|
| H1 (Page Title) | `text-2xl` (24px) | `font-semibold` | `leading-tight` |
| H2 (Section) | `text-xl` (20px) | `font-semibold` | `leading-tight` |
| H3 (Card Title) | `text-lg` (18px) | `font-medium` | `leading-normal` |
| Body | `text-base` (16px) | `font-normal` | `leading-relaxed` |
| Small | `text-sm` (14px) | `font-normal` | `leading-normal` |
| Label | `text-sm` (14px) | `font-medium` | `leading-none` |

---

## 4. Spacing & Layout

### Border Radius Scale

| Token | Value | Usage |
|-------|-------|-------|
| `rounded-sm` | `calc(var(--radius) * 0.6)` | ~6px - Small elements |
| `rounded-md` | `calc(var(--radius) * 0.8)` | ~8px - Inputs, buttons |
| `rounded-lg` | `var(--radius)` | ~10px - Cards |
| `rounded-xl` | `calc(var(--radius) * 1.4)` | ~14px - Large cards |
| `rounded-2xl` | `calc(var(--radius) * 1.8)` | ~18px - Modals |
| `rounded-full` | `9999px` | Pills, badges |

### Spacing Scale (Tailwind default)

| Token | Value | Usage |
|-------|-------|-------|
| `gap-2` | `0.5rem` (8px) | Tight grouping |
| `gap-4` | `1rem` (16px) | Standard spacing |
| `gap-6` | `1.5rem` (24px) | Section spacing |
| `gap-8` | `2rem` (32px) | Large sections |
| `p-4` | `1rem` (16px) | Card padding |
| `p-6` | `1.5rem` (24px) | Generous padding |

---

## 5. Components

### Button Variants

```tsx
// Primary - for main actions
<Button>Submit</Button>

// Secondary - for alternative actions  
<Button variant="secondary">Cancel</Button>

// Destructive - for reject/delete
<Button variant="destructive">Reject</Button>

// Outline - for tertiary actions
<Button variant="outline">View Details</Button>

// Ghost - for subtle actions
<Button variant="ghost">Edit</Button>
```

### Card Structure

```tsx
<Card>
  <CardHeader>
    <CardTitle>Card Title</CardTitle>
    <CardDescription>Card description</CardDescription>
  </CardHeader>
  <CardContent>
    Card content
  </CardContent>
  <CardFooter>
    Card actions
  </CardFooter>
</Card>
```

### Input Fields

```tsx
<Field>
  <FieldLabel>Label</FieldLabel>
  <Input placeholder="Enter text..." />
  <FieldDescription>Helpful text</FieldDescription>
  <FieldError>Error message</FieldError>
</Field>
```

### Status Badges

```tsx
<Badge variant="default">Default</Badge>
<Badge variant="secondary">Secondary</Badge>
<Badge variant="destructive">Destructive</Badge>
<Badge variant="outline">Outline</Badge>
```

### Data Table

```tsx
<Table>
  <TableHeader>
    <TableRow>
      <TableHead>Column</TableHead>
    </TableRow>
  </TableHeader>
  <TableBody>
    <TableRow>
      <TableCell>Data</TableCell>
    </TableRow>
  </TableBody>
</Table>
```

---

## 6. Screen Specifications

### 6.1 Login Page

**Layout:**
- Centered card (max-width: 28rem / 448px)
- Full viewport height, centered content
- Card with header, form content, footer actions

**Components:**
- `Card` wrapper
- `CardHeader` with title "Warehouse Login"
- `Field` for username + password
- `Input` fields
- `Button` primary for "Sign In"
- Link for "Forgot Password?"

### 6.2 Dashboard

**Layout:**
- Top navigation bar
- Page title "Submission Dashboard"
- Stats cards row (3 cards)
- Data table with submissions
- "New Submission" button (top right)

**Components:**
- `Table` with columns: ID, Warehouse Name, Location, Budget, Status, Step, Actions
- `Badge` for status (pending/approved/rejected)
- `Card` for stats
- `Button` primary for new submission

### 6.3 New Submission Form

**Layout:**
- Single column (max-width: 800px)
- Card-based sections
- Form with validation

**Sections:**
1. Warehouse Information (name, address, dropdowns)
2. Coordinates (lat/lng inputs, map preview)
3. Budget (currency input)
4. Documents (file upload)

**Components:**
- `Card` sections
- `Field` + `Input` for form fields
- `Button` for actions

### 6.4 Submission Detail

**Layout:**
- Two-column grid (70% / 30%)
- Left: Info cards, documents, map
- Right: Approval timeline, action buttons

**Components:**
- `Card` for each section
- `Table` for documents
- `Badge` for status
- `Button` group for approve/reject

---

## 7. Installation Commands

```bash
# Install core components
bunx shadcn@latest add @shadcn/button
bunx shadcn@latest add @shadcn/card
bunx shadcn@latest add @shadcn/input
bunx shadcn@latest add @shadcn/label
bunx shadcn@latest add @shadcn/form
bunx shadcn@latest add @shadcn/table
bunx shadcn@latest add @shadcn/badge
bunx shadcn@latest add @shadcn/dialog
bunx shadcn@latest add @shadcn/separator
bunx shadcn@latest add @shadcn/scroll-area
bunx shadcn@latest add @shadcn/sidebar
```

---

## 8. Design Principles

### Do
- Use semantic color tokens consistently
- Maintain proper contrast ratios
- Use card-based layouts for grouping
- Keep forms single-column when possible
- Show clear visual feedback for states

### Don't
- Don't use more than 3 button variants per screen
- Don't mix custom colors with theme tokens
- Don't add borders where spacing works
- Don't overcrowd tables - use pagination
- Don't ignore empty states

---

## 9. Responsive Breakpoints

| Breakpoint | Width | Behavior |
|------------|-------|----------|
| Mobile | < 640px | Single column, stacked layout |
| Tablet | 640px - 1024px | Two column where applicable |
| Desktop | > 1024px | Full multi-column layout |

---

## 10. Stitch MCP Project

**Project ID**: `4021187913905029327`
**Project Title**: Warehouse Approval System
**Design System**: shadcn-ui-internal
**Model**: GEMINI_3_1_PRO

### Screen Naming Convention
All screens use numbered prefixes for easy identification:
- `01-LOGIN` - Login page
- `02-DASHBOARD` - Dashboard with submissions table
- `03-FORM` - New submission form
- `04-DETAIL` - Submission detail view
