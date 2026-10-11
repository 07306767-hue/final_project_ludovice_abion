import 'package:flutter/material.dart';

/// Atom: the bold indigo "PHP xx.xx" price text on a catalog card.
class PriceLabel extends StatelessWidget {
  final double price;

  const PriceLabel(this.price, {super.key});

  @override
  Widget build(BuildContext context) {
    return Text(
      'PHP ${price.toStringAsFixed(2)}',
      style: const TextStyle(
        fontSize: 15,
        fontWeight: FontWeight.bold,
        color: Colors.indigo,
      ),
    );
  }
}
