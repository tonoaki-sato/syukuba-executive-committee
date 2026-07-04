import os
import re
import urllib.request
import base64
import requests
import markdown
import zlib
from xhtml2pdf import pisa
import shutil

# Paths configuration
BASE_DIR = "/opt/project/syukuba-executive-committee"
MD_PATH = os.path.join(BASE_DIR, "docs/patrol/patrol_security_plan.md")
PDF_PATH = os.path.join(BASE_DIR, "docs/patrol/patrol_security_plan.pdf")
TEMP_DIR = os.path.join(BASE_DIR, "docs/patrol/temp_pdf")

def setup_temp_dir():
    if os.path.exists(TEMP_DIR):
        shutil.rmtree(TEMP_DIR)
    os.makedirs(TEMP_DIR, exist_ok=True)

def download_font():
    print("Downloading IPAexGothic font...")
    font_url = "https://raw.githubusercontent.com/taichi/umlbot/master/.fonts/ipaexg.ttf"
    ttf_path = os.path.join(TEMP_DIR, "ipaexg.ttf")
    
    # Download ttf file directly
    headers = {"User-Agent": "Mozilla/5.0"}
    req = urllib.request.Request(font_url, headers=headers)
    with urllib.request.urlopen(req) as response, open(ttf_path, "wb") as out_file:
        shutil.copyfileobj(response, out_file)
                
    if not os.path.exists(ttf_path):
        raise FileNotFoundError("ipaexg.ttf download failed")
    print("Font downloaded successfully.")
    return ttf_path

def render_mermaid(mermaid_code, index):
    # Normalize line endings to \n and strip whitespace
    clean_code = mermaid_code.replace("\r\n", "\n").replace("\r", "\n").strip()
    print(f"Rendering Mermaid diagram {index} using Kroki.io...")
    
    # Compress using standard zlib
    compressed = zlib.compress(clean_code.encode("utf-8"))
    b64_str = base64.urlsafe_b64encode(compressed).decode("ascii")
    
    url = f"https://kroki.io/mermaid/png/{b64_str}"
    img_path = os.path.join(TEMP_DIR, f"mermaid_{index}.png")
    
    # Fetch rendered image
    response = requests.get(url, headers={"User-Agent": "Mozilla/5.0"}, timeout=30)
    if response.status_code == 200:
        with open(img_path, "wb") as f:
            f.write(response.content)
        print(f"Mermaid diagram {index} saved to {img_path}")
        return img_path
    else:
        error_msg = response.text[:200]
        raise Exception(f"Failed to render Mermaid diagram {index}: HTTP {response.status_code} - {error_msg}")

def preprocess_markdown(md_text):
    # Match ```mermaid blocks
    mermaid_pattern = re.compile(r"```mermaid\s*\n(.*?)\n```", re.DOTALL)
    mermaid_matches = mermaid_pattern.findall(md_text)
    
    temp_md = md_text
    for i, m_code in enumerate(mermaid_matches, start=1):
        img_path = render_mermaid(m_code, i)
        # Using the direct absolute path
        img_url_markdown = f"![Mermaid Diagram {i}]({img_path})"
        
        # Replace block using simple string replace for stability (handling CRLF/LF)
        full_block_lf = f"```mermaid\n{m_code}\n```"
        full_block_crlf = f"```mermaid\r\n{m_code}\r\n```"
        
        if full_block_lf in temp_md:
            temp_md = temp_md.replace(full_block_lf, img_url_markdown)
        elif full_block_crlf in temp_md:
            temp_md = temp_md.replace(full_block_crlf, img_url_markdown)
        else:
            # Fallback regex substitution
            temp_md = re.sub(r"```mermaid\s*\n.*?\n```", img_url_markdown, temp_md, count=1, flags=re.DOTALL)
            
    return temp_md

def convert_alerts_in_html(html_text):
    # Regex to capture blockquote sections
    # Markdown converts blockquotes to <blockquote>...</blockquote>
    # If the block contains [!WARNING], we style it as a custom alert box
    def replace_blockquote(match):
        content = match.group(1)
        if "[!WARNING]" in content:
            clean_content = content.replace("[!WARNING]", "").strip()
            # Clean up potential leading break tags or paragraphs
            clean_content = re.sub(r"^<br\s*/?>", "", clean_content).strip()
            clean_content = re.sub(r"^<p>", "", clean_content).strip()
            # Ensure p tags inside warning are structured nicely
            return f'<div class="alert-warning"><p><strong>【警告】</strong></p><p>{clean_content}</div>'
        return match.group(0)

    html_text = re.sub(r"<blockquote>(.*?)</blockquote>", replace_blockquote, html_text, flags=re.DOTALL)
    return html_text

def link_callback(uri, rel):
    """
    Convert HTML images/assets links to local filesystem paths for xhtml2pdf.
    """
    # If it is a web URL, return as is
    if uri.startswith("http://") or uri.startswith("https://"):
        return uri
        
    # Strip file:// prefix if present
    if uri.startswith("file://"):
        uri = uri[7:]
        
    # Clean any potential query parameters
    uri = uri.split('?')[0]
    
    # If it's an absolute path that exists, return it
    if os.path.isabs(uri) and os.path.exists(uri):
        return uri
        
    # Otherwise, resolve relative to the project base dir
    resolved = os.path.join(BASE_DIR, uri)
    if os.path.exists(resolved):
        return resolved
        
    return uri

def build_pdf(html_content, ttf_path):
    print("Building PDF...")
    
    # Custom CSS for A4 page styling, tables, borders, and margins
    css_content = f"""
    @page {{
        size: a4;
        margin: 2cm;
    }}
    @font-face {{
        font-family: 'IPAexGothic';
        src: url('{ttf_path}');
    }}
    body {{
        font-family: 'IPAexGothic', sans-serif;
        font-size: 10pt;
        line-height: 1.6;
        color: #333333;
    }}
    h1, h2, h3, h4 {{
        font-family: 'IPAexGothic';
        color: #111111;
        font-weight: bold;
    }}
    h1 {{
        font-size: 18pt;
        border-bottom: 2px solid #E65100;
        padding-bottom: 6px;
        margin-top: 0px;
        margin-bottom: 20px;
    }}
    h2 {{
        font-size: 13pt;
        border-bottom: 1px solid #cccccc;
        padding-bottom: 4px;
        margin-top: 25px;
        margin-bottom: 12px;
    }}
    h3 {{
        font-size: 11pt;
        margin-top: 18px;
        margin-bottom: 8px;
    }}
    p {{
        margin-top: 0px;
        margin-bottom: 10px;
    }}
    table {{
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
        margin-bottom: 20px;
        font-size: 9.5pt;
    }}
    th, td {{
        border: 1px solid #cccccc;
        padding: 8px 10px;
        text-align: left;
        vertical-align: middle;
    }}
    th {{
        background-color: #f5f5f5;
        font-weight: bold;
    }}
    ul, ol {{
        margin-top: 0px;
        margin-bottom: 10px;
        padding-left: 20px;
    }}
    li {{
        margin-bottom: 4px;
    }}
    .alert-warning {{
        padding: 12px;
        margin-top: 15px;
        margin-bottom: 15px;
        border-left: 4px solid #ff9800;
        background-color: #fff9c4;
    }}
    .alert-warning p {{
        margin-bottom: 5px;
    }}
    img {{
        max-width: 100%;
        height: auto;
        margin-top: 15px;
        margin-bottom: 15px;
    }}
    hr {{
        border: 0;
        border-top: 1px solid #eeeeee;
        margin-top: 20px;
        margin-bottom: 20px;
    }}
    """
    
    full_html = f"""<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
{css_content}
</style>
</head>
<body>
{html_content}
</body>
</html>
"""

    with open(PDF_PATH, "wb") as f:
        pisa_status = pisa.CreatePDF(full_html, dest=f, link_callback=link_callback)
        
    if pisa_status.err:
        raise Exception(f"Failed to generate PDF: pisa errors occurred")
    print(f"PDF generated successfully at {PDF_PATH}")

def main():
    try:
        setup_temp_dir()
        
        # 1. Font download
        ttf_path = download_font()
        
        # 2. Read Markdown
        with open(MD_PATH, "r", encoding="utf-8") as f:
            md_text = f.read()
            
        # 3. Preprocess Markdown (render Mermaid and replace)
        print("Preprocessing Markdown...")
        md_preprocessed = preprocess_markdown(md_text)
        
        # 4. Convert to HTML
        print("Converting Markdown to HTML...")
        html_raw = markdown.markdown(
            md_preprocessed, 
            extensions=['tables', 'fenced_code', 'nl2br']
        )
        
        # 5. Process alerts in HTML
        html_final = convert_alerts_in_html(html_raw)
        
        # 6. Build PDF
        build_pdf(html_final, ttf_path)
        
    except Exception as e:
        print(f"Error during PDF compilation: {e}")
        raise e
    finally:
        # Clean up temp files
        print("Cleaning up temporary files...")
        if os.path.exists(TEMP_DIR):
            shutil.rmtree(TEMP_DIR)
        print("Done.")

if __name__ == "__main__":
    main()
