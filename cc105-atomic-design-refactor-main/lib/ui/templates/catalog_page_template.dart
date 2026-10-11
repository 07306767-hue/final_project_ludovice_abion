import 'package:flutter/material.dart';

/// Template: the page-level layout skeleton — an app bar plus a
/// scrollable column of three slots. It knows how to *arrange* whatever
/// widgets it's handed, but nothing about what a "product" is, which is
/// why it never imports the Product model or accepts anything more
/// specific than [Widget].
class CatalogPageTemplate extends StatelessWidget {
  final PreferredSizeWidget appBar;
  final Widget searchSection;
  final Widget catalogSection;
  final Widget formSection;

  const CatalogPageTemplate({
    super.key,
    required this.appBar,
    required this.searchSection,
    required this.catalogSection,
    required this.formSection,
  });

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: appBar,
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(12),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            searchSection,
            const SizedBox(height: 16),
            catalogSection,
            const Divider(height: 32, thickness: 1),
            formSection,
          ],
        ),
      ),
    );
  }
}
