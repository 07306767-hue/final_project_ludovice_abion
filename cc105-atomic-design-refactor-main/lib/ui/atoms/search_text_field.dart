import 'package:flutter/material.dart';

/// Atom: a plain, uncontrolled text field. It forwards every keystroke via
/// [onChanged] and owns no state of its own — this intentionally matches
/// the original screen's behavior, including the fact that the visible
/// text does not clear itself when the search query is reset elsewhere.
class SearchTextField extends StatelessWidget {
  final ValueChanged<String> onChanged;

  const SearchTextField({super.key, required this.onChanged});

  @override
  Widget build(BuildContext context) {
    return TextField(
      onChanged: onChanged,
      decoration: const InputDecoration(hintText: 'Type a product name...'),
    );
  }
}
