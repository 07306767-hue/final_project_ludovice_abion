import 'package:flutter/material.dart';

/// Atom: the app bar. Its title and color are fixed and never change in
/// response to app state, so it needs nothing more than a
/// [StatelessWidget].
class CatalogAppBar extends StatelessWidget implements PreferredSizeWidget {
  const CatalogAppBar({super.key});

  @override
  Widget build(BuildContext context) {
    return AppBar(
      title: const Text(
        'Messy Catalog',
        style: TextStyle(
          fontSize: 20,
          fontWeight: FontWeight.bold,
          color: Colors.white,
        ),
      ),
      backgroundColor: Colors.indigo,
    );
  }

  @override
  Size get preferredSize => const Size.fromHeight(kToolbarHeight);
}
