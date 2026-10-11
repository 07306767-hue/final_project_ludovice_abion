import 'package:flutter/material.dart';

import '../atoms/app_primary_button.dart';
import '../atoms/delete_icon_button.dart';

/// Molecule: groups the "Add to Cart" button and the delete icon button
/// into the single actions unit shown on the right of each catalog card.
class ProductActions extends StatelessWidget {
  final VoidCallback onAddToCart;
  final VoidCallback onDelete;

  const ProductActions({
    super.key,
    required this.onAddToCart,
    required this.onDelete,
  });

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        AppPrimaryButton(label: 'Add to Cart', onPressed: onAddToCart),
        const SizedBox(height: 6),
        DeleteIconButton(onPressed: onDelete),
      ],
    );
  }
}
