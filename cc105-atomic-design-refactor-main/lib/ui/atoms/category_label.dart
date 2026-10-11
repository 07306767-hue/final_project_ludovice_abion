import 'package:flutter/material.dart';

/// Atom: the small grey category text on a catalog card.
class CategoryLabel extends StatelessWidget {
  final String category;

  const CategoryLabel(this.category, {super.key});

  @override
  Widget build(BuildContext context) {
    return Text(
      category,
      style: TextStyle(fontSize: 13, color: Colors.grey.shade600),
    );
  }
}
