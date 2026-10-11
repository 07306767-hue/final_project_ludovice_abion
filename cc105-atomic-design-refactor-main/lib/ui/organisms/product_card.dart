import 'package:flutter/material.dart';

import '../../models/product.dart';
import '../atoms/product_icon_avatar.dart';
import '../molecules/product_actions.dart';
import '../molecules/product_info.dart';

/// Organism: a complete catalog card — a complex section built from
/// molecules and an atom. It knows how to lay a single product out, but
/// it does not own the product list itself; it only reports user intent
/// upward via callbacks.
class ProductCard extends StatelessWidget {
  final Product product;
  final VoidCallback onAddToCart;
  final VoidCallback onDelete;

  const ProductCard({
    super.key,
    required this.product,
    required this.onAddToCart,
    required this.onDelete,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.only(bottom: 10),
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(8),
        border: Border.all(color: Colors.grey.shade300),
        boxShadow: [
          BoxShadow(
            color: Colors.grey.withValues(alpha: 0.3),
            blurRadius: 4,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Row(
        children: [
          ProductIconAvatar(icon: product.icon),
          const SizedBox(width: 12),
          Expanded(child: ProductInfo(product: product)),
          ProductActions(onAddToCart: onAddToCart, onDelete: onDelete),
        ],
      ),
    );
  }
}
