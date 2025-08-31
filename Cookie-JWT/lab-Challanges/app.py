from flask import Flask, render_template, request, redirect, make_response
import jwt
import datetime

app = Flask(__name__)
SECRET_KEY = "supersemar"

@app.route("/", methods=["GET", "POST"])
def login():
    if request.method == "POST":
        username = request.form.get("username", "").strip()
        password = request.form.get("password", "").strip()

        if not username or not password:
            return render_template("login.html", error="Username dan Password wajib diisi!")

        # Buat JWT default -> login=fail
        payload = {
            "login": "fail",
            "user": username,
            "exp": datetime.datetime.utcnow() + datetime.timedelta(minutes=10)
        }
        token = jwt.encode(payload, SECRET_KEY, algorithm="HS256")

        resp = make_response(redirect("/home"))
        resp.set_cookie("token", token)  # Simpan JWT di cookie
        return resp

    return render_template("login.html", error=None)


@app.route("/home")
def home():
    token = request.cookies.get("token", "")
    if not token:
        return "Token Failed", 401

    try:
        decoded = jwt.decode(token, SECRET_KEY, algorithms=["HS256"])
        if decoded.get("login") != "success":
            return "Token Failed", 401
    except Exception:
        return "Token Failed", 401

    # Sudah berhasil login → kasih cookie admin=0 default
    resp = make_response(render_template("home.html"))
    resp.set_cookie("admin", "0")
    return resp


if __name__ == "__main__":
    app.run(host="0.0.0.0", port=5000)

