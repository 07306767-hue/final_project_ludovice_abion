import 'package:flutter/material.dart';

import '../../models/product.dart';
import '../atoms/category_label.dart';
import '../atoms/price_label.dart';
import '../atoms/product_name_label.dart';

/// Molecule: groups the name, category and price atoms into the single
/// "product info" unit shown inside a catalog card.
class ProductInfo extends StatelessWidget {
  final Product product;

  const ProductInfo({super.key, required this.product});

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        ProductNameLabel(product.name),
        const SizedBox(height: 4),
        CategoryLabel(product.category),
        const SizedBox(height: 4),
        PriceLabel(product.price),
      ],
    );
  }
}
