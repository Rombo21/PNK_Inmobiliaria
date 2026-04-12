import sys
from pypdf import PdfReader

try:
    reader = PdfReader("d:/wamp64/www/PNK_Inmobiliaria/REQUERIMIENTOS_PRIMERA_ENTREGA.pdf")
    text = ""
    for page in reader.pages:
        text += page.extract_text() + "\n"
    with open("d:/wamp64/www/PNK_Inmobiliaria/pdf_content.txt", "w", encoding="utf-8") as f:
        f.write(text)
except Exception as e:
    print("Error reading PDF:", e)
