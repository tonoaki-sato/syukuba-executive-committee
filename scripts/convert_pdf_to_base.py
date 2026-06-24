import fitz
import sys

if len(sys.argv) < 3:
    print("Usage: python3 convert_pdf_to_base.py <input_pdf> <output_png>")
    sys.exit(1)

input_pdf = sys.argv[1]
output_png = sys.argv[2]

try:
    doc = fitz.open(input_pdf)
    page = doc[0]
    original_w = page.rect.width
    
    # Render to 1600px width for retina display high resolution
    zoom = 1600.0 / original_w
    matrix = fitz.Matrix(zoom, zoom)
    
    pix = page.get_pixmap(matrix=matrix)
    pix.save(output_png)
    print("Success")
    sys.exit(0)
except Exception as e:
    print(f"Error: {str(e)}", file=sys.stderr)
    sys.exit(1)
