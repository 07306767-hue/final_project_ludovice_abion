import 'package:flutter/material.dart';

/// Atom: a generic labeled [TextFormField]. Controller, label, validator,
/// keyboard type and line count are all supplied from outside, so this
/// atom holds no opinion about *what* is valid or *what* the data means —
/// it only renders. Reused for the Name, Price, and Description fields.
class LabeledTextFormField extends StatelessWidget {
  final TextEditingController controller;
  final String label;
  final String? Function(String?)? validator;
  final TextInputType? keyboardType;
  final int maxLines;
  final bool alignLabelWithHint;

  const LabeledTextFormField({
    super.key,
    required this.controller,
    required this.label,
    this.validator,
    this.keyboardType,
    this.maxLines = 1,
    this.alignLabelWithHint = false,
  });

  @override
  Widget build(BuildContext context) {
    return TextFormField(
      controller: controller,
      keyboardType: keyboardType,
      maxLines: maxLines,
      decoration: InputDecoration(
        labelText: label,
        alignLabelWithHint: alignLabelWithHint,
      ),
      validator: validator,
    );
  }
}
