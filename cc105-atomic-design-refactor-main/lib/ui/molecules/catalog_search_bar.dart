import 'package:flutter/material.dart';

import '../atoms/app_heading.dart';
import '../atoms/search_text_field.dart';

/// Molecule: the heading + input field that together form the single
/// "search" unit the user perceives. It holds no state of its own (every
/// keystroke is forwarded straight to [onChanged]), but it groups two
/// atoms into one reusable functional unit.
class CatalogSearchBar extends StatelessWidget {
  final ValueChanged<String> onChanged;

  const CatalogSearchBar({super.key, required this.onChanged});

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const AppHeading('Search Products'),
        const SizedBox(height: 8),
        SearchTextField(onChanged: onChanged),
      ],
    );
  }
}
