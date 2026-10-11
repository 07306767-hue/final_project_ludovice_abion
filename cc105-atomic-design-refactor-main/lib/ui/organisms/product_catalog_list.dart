import 'package:flutter/material.dart';

import '../../models/product.dart';
import '../atoms/app_heading.dart';
import 'product_card.dart';

/// Organism: the "Catalog" heading plus the filtered list of product
/// cards. Filtering by [searchQuery] is local presentation logic — it
/// derives a view of the data it was given, but it never mutates or owns
/// the underlying product list. That list is still supplied (and owned)
/// by the Page above.
class ProductCatalogList extends StatelessWidget {
  final List<Product> products;
  final String searchQuery;
  final ValueChanged<Product> onAddToCart;
  final ValueChanged<Product> onDelete;

  const ProductCatalogList({
    super.key,
    required this.products,
    required this.searchQuery,
    required this.onAddToCart,
    required this.onDelete,
  });

  @override
  Widget build(BuildContext context) {
    final filtered = products
        .where((p) => p.name.toLowerCase().contains(searchQuery.toLowerCase()))
        .toList();

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const AppHeading('Catalog'),
        const SizedBox(height: 8),
        Column(
          children: filtered
              .map(
                (product) => ProductCard(
                  product: product,
                  onAddToCart: () => onAddToCart(product),
                  onDelete: () => onDelete(product),
                ),
              )
              .toList(),
        ),
      ],
    );
  }
}
