import 'package:flutter/material.dart';

import '../atoms/app_heading.dart';
import '../atoms/app_primary_button.dart';
import '../atoms/category_dropdown_field.dart';
import '../atoms/labeled_text_form_field.dart';

/// Fired once the form has validated successfully. Only raw field values
/// are reported upward — this organism has no idea how a Product is built
/// or stored, and it never touches the product catalog directly.
typedef ProductFormSubmit = void Function({
  required String name,
  required double price,
  required String category,
  required String description,
});

/// Organism: the entire "Add New Product" section. It owns the pieces of
/// state that belong to the *form itself* — the [GlobalKey<FormState>],
/// text controllers and selected category — and runs field validation.
/// This is local UI logic, not core app data, so it's allowed here even
/// though Organisms may not own the product catalog. On a valid submit it
/// simply reports the entered values upward via [onSubmit] and resets its
/// own fields; deciding what happens to the catalog is entirely up to the
/// Page.
class AddProductForm extends StatefulWidget {
  final ProductFormSubmit onSubmit;

  const AddProductForm({super.key, required this.onSubmit});

  @override
  State<AddProductForm> createState() => _AddProductFormState();
}

class _AddProductFormState extends State<AddProductForm> {
  static const _categories = ['Electronics', 'Home', 'Office', 'Accessories'];

  final _formKey = GlobalKey<FormState>();
  final _nameController = TextEditingController();
  final _priceController = TextEditingController();
  final _descriptionController = TextEditingController();
  String _selectedCategory = 'Electronics';

  @override
  void dispose() {
    _nameController.dispose();
    _priceController.dispose();
    _descriptionController.dispose();
    super.dispose();
  }

  String? _validateName(String? value) {
    if (value == null || value.trim().isEmpty) {
      return 'Product name is required';
    }
    return null;
  }

  String? _validatePrice(String? value) {
    if (value == null || value.trim().isEmpty) {
      return 'Price is required';
    }
    final parsed = double.tryParse(value);
    if (parsed == null) {
      return 'Price must be a number';
    }
    if (parsed <= 0) {
      return 'Price must be greater than zero';
    }
    return null;
  }

  void _handleSubmit() {
    if (!_formKey.currentState!.validate()) return;

    widget.onSubmit(
      name: _nameController.text,
      price: double.parse(_priceController.text),
      category: _selectedCategory,
      description: _descriptionController.text,
    );

    setState(() {
      _nameController.clear();
      _priceController.clear();
      _descriptionController.clear();
      _selectedCategory = 'Electronics';
    });
  }

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const AppHeading('Add New Product'),
        const SizedBox(height: 12),
        Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              LabeledTextFormField(
                controller: _nameController,
                label: 'Product Name',
                validator: _validateName,
              ),
              const SizedBox(height: 12),
              LabeledTextFormField(
                controller: _priceController,
                label: 'Price',
                keyboardType: TextInputType.number,
                validator: _validatePrice,
              ),
              const SizedBox(height: 12),
              CategoryDropdownField(
                value: _selectedCategory,
                categories: _categories,
                onChanged: (value) {
                  setState(() {
                    _selectedCategory = value ?? 'Electronics';
                  });
                },
              ),
              const SizedBox(height: 12),
              LabeledTextFormField(
                controller: _descriptionController,
                label: 'Description',
                maxLines: 3,
                alignLabelWithHint: true,
              ),
              const SizedBox(height: 16),
              AppPrimaryButton(
                label: 'Submit Product',
                onPressed: _handleSubmit,
                fullWidth: true,
                padding: const EdgeInsets.symmetric(vertical: 14),
                textStyle: const TextStyle(
                  fontSize: 16,
                  fontWeight: FontWeight.bold,
                  color: Colors.white,
                ),
              ),
            ],
          ),
        ),
      ],
    );
  }
}
