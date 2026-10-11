import 'package:flutter/material.dart';

/// Atom: the red delete icon button on a catalog card. It only reports
/// that it was pressed — deciding what deletion means is the caller's job.
class DeleteIconButton extends StatelessWidget {
  final VoidCallback onPressed;

  const DeleteIconButton({super.key, required this.onPressed});

  @override
  Widget build(BuildContext context) {
    return IconButton(
      onPressed: onPressed,
      icon: const Icon(Icons.delete_outline, color: Colors.red),
    );
  }
}
