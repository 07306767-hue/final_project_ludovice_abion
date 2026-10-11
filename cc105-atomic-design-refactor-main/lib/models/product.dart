import 'package:flutter/material.dart';

/// The app's single core data entity. Only [Page]s are allowed to hold a
/// collection of these; every other layer just receives one (or a list)
/// as a plain parameter.
class Product {
  final int id;
  final String name;
  final double price;
  final String category;
  final IconData icon;
  final String description;

  const Product({
    required this.id,
    required this.name,
    required this.price,
    required this.category,
    required this.icon,
    this.description = '',
  });
}
