# Justification — Refactor the Spaghetti Screen

Each paragraph below names the level a widget was placed at and the specific rule
from the activity sheet that puts it there.

## Supporting layer (not a UI level)

**`Product` (lib/models/product.dart).** This isn't an Atomic Design level — it's
the data model the rules keep referencing ("Templates ... must never import a data
model directly", "Pages are the only place allowed to hold the actual product
list"). Pulling the product `Map<String, dynamic>` out into a typed `Product` class
made those two rules checkable: I could grep for `import '.../product.dart'`
across `templates/` and confirm it never appears there, and confirm the only place
that holds a `List<Product>` is `CatalogPage`.

## Atoms

**`AppHeading`.** Renders one string in a fixed bold style. It has no state
beyond the text it displays and no logic — a pure `StatelessWidget` — so it meets
the atom rule exactly and is reused for all three section headings ("Search
Products", "Catalog", "Add New Product") instead of three copies of the same
`Text` + `TextStyle`.

**`AppPrimaryButton`.** The indigo/white button styling appeared twice
(`Add to Cart`, `Submit Product`) with only the label, padding, text style and
full-width behavior differing. It takes all of that as parameters and makes no
decision about *what* pressing it does — that's the caller's `onPressed`. No
internal state, no business logic → atom.

**`ProductIconAvatar`.** A 56×56 decorated box around an `Icon`. It only reads the
`IconData` it's given; it doesn't know what a product is or where the icon came
from. Stateless, logic-free rendering → atom.

**`DeleteIconButton`.** Wraps a single `IconButton` with fixed red delete styling
and forwards presses via `onPressed`. It doesn't remove anything itself — the atom
rule says atoms hold no logic beyond rendering, so the actual removal stays in the
Page, which owns the list.

**`ProductNameLabel`, `CategoryLabel`, `PriceLabel`.** Three single-purpose text
atoms, one per distinct piece of text on a card (name, category, formatted
price). Each is a one-line `StatelessWidget` with a fixed `TextStyle` and no
state — splitting them individually (rather than one "ProductText" atom) keeps
each one meaningfully reusable on its own, e.g. `PriceLabel` could show up
anywhere a price needs the same indigo/bold treatment.

**`SearchTextField`.** A bare `TextField` that forwards `onChanged` and owns no
`TextEditingController` or internal state. Because it's uncontrolled, it also
faithfully reproduces the original's quirk of not visually clearing when
`_searchQuery` is reset after a product is added — matching the "must behave
identically" requirement instead of "fixing" a bug that wasn't asked for.

**`LabeledTextFormField`.** One generic `TextFormField` wrapper reused for the
Name, Price, and Description inputs. It accepts `validator`, `keyboardType`,
`maxLines`, and `controller` as parameters rather than deciding validation rules
itself — the atom renders and reports `onChanged`/validation results, but the
actual rules (e.g. "price must be > 0") are supplied from the organism that owns
them. This is what let three near-identical `TextFormField` blocks in the
original collapse into one reusable atom.

**`CategoryDropdownField`.** Same reasoning as above: a `DropdownButtonFormField`
that receives `value`, `categories`, and `onChanged` and holds no state of its
own — the organism above it decides what "changing category" means.

**`CatalogAppBar`.** The `AppBar`'s title and color never change based on app
state, so despite technically being a "complex" Material widget, its actual
responsibility here is 100% static rendering — no logic, no data — which is
squarely inside the atom rule rather than the organism rule (organisms combine
*multiple* atoms/molecules into a section; this is a single fixed widget).

## Molecules

**`CatalogSearchBar`.** Groups `AppHeading` + `SearchTextField` into the single
functional unit a user thinks of as "the search box." It doesn't hold local state
itself, but the molecule rule doesn't require that — it only permits it. What
makes it a molecule rather than an atom is that it *composes two atoms* into one
reusable unit, matching the definition ("small groups of atoms functioning as a
single unit").

**`ProductInfo`.** Groups `ProductNameLabel`, `CategoryLabel`, and `PriceLabel`
into the info column shown on every card. Same rule as above: it's a small,
fixed composition of atoms with no business logic of its own — it just lays out
whatever `Product` it's handed.

**`ProductActions`.** Groups `AppPrimaryButton` ("Add to Cart") and
`DeleteIconButton` into the action column on the right of a card. It holds no
state and no business logic (it doesn't decide what "add to cart" or "delete"
actually do) — it only composes two atoms and forwards two callbacks, which is
exactly the molecule boundary.

## Organisms

**`ProductCard`.** A full catalog row — icon avatar + product info + actions —
is a "complex section composed of molecules and atoms," which is the organism
rule verbatim. It receives one `Product` and two callbacks and has no
data-fetching or catalog ownership of its own, so it doesn't cross into Page
territory.

**`ProductCatalogList`.** Owns the search-filtering *computation*
(`products.where(...)`) but not the *data* — the `List<Product>` it filters is
handed in every build, and it's never mutated here. This matches "Organisms may
contain local logic but should not directly own the app's core data": filtering
is local logic derived from data it was given, not ownership of that data.

**`AddProductForm`.** This is the one place the rules get subtle, so it gets the
longest justification. The form needs a `GlobalKey<FormState>`, three
`TextEditingController`s, and the currently-selected category to function at
all — none of that is "the app's core data" (the product catalog), it's local UI
state that belongs to the form widget itself, so keeping it here doesn't violate
the organism rule. Validation (`_validateName`, `_validatePrice`) also lives
here, next to the fields it validates, rather than in the Page. What the
organism does *not* do is decide what a successful submission means: it doesn't
construct a `Product`, doesn't assign an `id`, doesn't touch `_products`, and
doesn't show the confirmation `SnackBar`. It only calls `onSubmit(name, price,
category, description)` — raw field values — and then clears its own fields.
That's the "clear Organism/Page boundary" the rubric asks for: the organism owns
the *form*, the Page owns the *data and the confirmation feedback that follows a
successful data change*.

## Template

**`CatalogPageTemplate`.** Accepts `appBar`, `searchSection`, `catalogSection`,
and `formSection` purely as `Widget`/`PreferredSizeWidget` slots and arranges
them in the `Scaffold` + `SingleChildScrollView` + `Column` structure. It never
imports `Product` and never sees a product, a search query, or a form value — it
only knows how to lay out three boxes and a divider, which is exactly what the
template rule asks for ("accept layout slots as parameters and must never import
a data model directly").

## Page

**`CatalogPage`.** The only file that declares `List<Product> _products`. It
owns `_searchQuery` and `_nextId`, and it's the only place that: (1) turns raw
form values into an actual `Product` (assigning the id and the default
`Icons.inventory_2`), (2) mutates `_products` via `setState`, and (3) shows both
`SnackBar`s (add-to-cart and add-to-catalog confirmation). Every one of those is
either "own the actual product list" or "wire real data downward" — the two
things the rules say only a Page may do.

## Note on something I was unsure how to classify

The submit flow's confirmation `SnackBar` was the one genuinely ambiguous piece.
It's arguably *part of the form's job* (the user just interacted with the form,
so the form "knows" a submission happened) — I could see an argument for having
`AddProductForm` show it directly. I placed it in `CatalogPage` instead, because
showing a green "added to catalog" message only makes sense *after* the product
has actually been added to `_products`, and only the Page can know that
succeeded (the organism doesn't touch the list at all). If a future requirement
ever needed the form to show feedback *before* a Page-level save finishes (e.g.
optimistic UI), I'd expect that confirmation logic to move into a callback result
rather than staying implicit — but for this activity's behavior, tying it to the
actual data mutation felt like the more defensible boundary.
