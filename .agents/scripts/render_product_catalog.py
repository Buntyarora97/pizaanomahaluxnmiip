from pathlib import Path
import fitz


source = Path("attached_assets/NXTLab-3_(1)_1791270454333.pdf")
output = Path(".agents/outputs/catalog-pages")
output.mkdir(parents=True, exist_ok=True)

document = fitz.open(source)
print(f"Pages: {document.page_count}")

for index, page in enumerate(document):
    image_path = output / f"page-{index + 1:02}.png"
    page.get_pixmap(matrix=fitz.Matrix(1.6, 1.6), alpha=False).save(image_path)
    print(f"\n--- PAGE {index + 1} ---")
    print(page.get_text().strip()[:1200])
    for image_number, image in enumerate(page.get_images(full=True), start=1):
        xref = image[0]
        pixmap = fitz.Pixmap(document, xref)
        soft_mask = image[1]
        if soft_mask:
            pixmap = fitz.Pixmap(pixmap, fitz.Pixmap(document, soft_mask))
        image_file = output / f"page-{index + 1:02}-image-{image_number:02}.png"
        pixmap.save(image_file)
        print(f"embedded image: {image_file} ({pixmap.width}x{pixmap.height}, alpha={pixmap.alpha})")

