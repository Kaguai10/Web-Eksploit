from flask import Flask, request, render_template
import subprocess

app = Flask(__name__)

@app.route("/", methods=["GET", "POST"])
def index():
    output = ""

    if request.method == "POST":
        url = request.form.get("url")

        try:
            result = subprocess.check_output(
                ["curl", "-s", url],
                stderr=subprocess.STDOUT,
                timeout=3
            )
            output = result.decode(errors="ignore")
        except Exception as e:
            output = str(e)

    return render_template("index.html", output=output)

if __name__ == "__main__":
    app.run(host="0.0.0.0", port=8080)          
