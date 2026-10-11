import 'package:flutter/material.dart';

/// Atom: the bold product-name text on a catalog card.
class ProductNameLabel extends StatelessWidget {
  final String name;

  const ProductNameLabel(this.name, {super.key});

  @override
  Widget build(BuildContext context) {
    return Text(
      name,
      style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w600),
    );
  }
}
