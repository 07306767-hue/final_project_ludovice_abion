import 'package:flutter/material.dart';

/// Atom: the 56x56 rounded icon box shown at the left of a catalog card.
/// Takes just the [IconData] it should render.
class ProductIconAvatar extends StatelessWidget {
  final IconData icon;

  const ProductIconAvatar({super.key, required this.icon});

  @override
  Widget build(BuildContext context) {
    return Container(
      width: 56,
      height: 56,
      decoration: BoxDecoration(
        color: Colors.indigo.shade50,
        borderRadius: BorderRadius.circular(8),
      ),
      child: Icon(icon, size: 32, color: Colors.indigo),
    );
  }
}
