import 'package:flutter/material.dart';

/// Atom: a bold section heading ("Search Products", "Catalog",
/// "Add New Product"). Pure presentation — a [StatelessWidget] with no
/// logic and no state beyond what it needs to render the text it's given.
class AppHeading extends StatelessWidget {
  final String text;

  const AppHeading(this.text, {super.key});

  @override
  Widget build(BuildContext context) {
    return Text(
      text,
      style: const TextStyle(
        fontSize: 20,
        fontWeight: FontWeight.bold,
        color: Colors.black87,
      ),
    );
  }
}
