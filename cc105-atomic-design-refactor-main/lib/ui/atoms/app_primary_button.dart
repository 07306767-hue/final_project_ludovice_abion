import 'package:flutter/material.dart';

/// Atom: the indigo/white "brand" button reused for both "Add to Cart"
/// and "Submit Product". It renders purely from the props it's handed —
/// label, callback, optional full-width and styling — and makes no
/// decisions of its own about what pressing it should do.
class AppPrimaryButton extends StatelessWidget {
  final String label;
  final VoidCallback onPressed;
  final bool fullWidth;
  final EdgeInsetsGeometry? padding;
  final TextStyle? textStyle;

  const AppPrimaryButton({
    super.key,
    required this.label,
    required this.onPressed,
    this.fullWidth = false,
    this.padding,
    this.textStyle,
  });

  @override
  Widget build(BuildContext context) {
    final button = ElevatedButton(
      onPressed: onPressed,
      style: ElevatedButton.styleFrom(
        backgroundColor: Colors.indigo,
        foregroundColor: Colors.white,
        padding: padding,
      ),
      child: Text(label, style: textStyle),
    );

    return fullWidth ? SizedBox(width: double.infinity, child: button) : button;
  }
}
