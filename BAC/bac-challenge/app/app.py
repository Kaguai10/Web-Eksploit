from flask import Flask, render_template, request, redirect, session
import json, os

app = Flask(__name__)
app.secret_key = 'supersecretkey'
USERS_FILE = 'users.json'

def load_users():
    if not os.path.exists(USERS_FILE):
        return []
    with open(USERS_FILE) as f:
        return json.load(f)

def save_users(users):
    with open(USERS_FILE, 'w') as f:
        json.dump(users, f, indent=2)

@app.route('/')
def index():
    return render_template('index.html')

@app.route('/register', methods=['GET', 'POST'])
def register():
    if request.method == 'POST':
        users = load_users()
        username = request.form['username']
        password = request.form['password']
        if any(u['username'] == username for u in users):
            return 'Username already exists.'
        user_id = len(users) + 1
        users.append({'id': user_id, 'username': username, 'password': password})
        save_users(users)
        return redirect('/login')
    return render_template('register.html')

@app.route('/login', methods=['GET', 'POST'])
def login():
    if request.method == 'POST':
        users = load_users()
        username = request.form['username']
        password = request.form['password']
        user = next((u for u in users if u['username'] == username and u['password'] == password), None)
        if user:
            session['user_id'] = user['id']
            session['username'] = user['username']
            return redirect('/profile')
        return 'Login failed.'
    return render_template('login.html')

@app.route('/profile')
def profile():
    if 'user_id' not in session:
        return redirect('/login')
    return redirect(f"/user?id={session['user_id']}")

@app.route('/user')
def user():
    if 'user_id' not in session:
        return redirect('/login')
    requested_id = request.args.get('id')
    users = load_users()
    user = next((u for u in users if str(u['id']) == requested_id), None)
    if user:
        flag = "flag{br0ken_access_control_horiz0nt4l}" if int(requested_id) == 1 else ""
        return render_template('profile.html', user=user, flag=flag)
    return "User not found."

@app.route('/admin')
def admin():
    if 'user_id' not in session:
        return redirect('/login')
    return render_template('admin.html', flag="flag{BRok3n_access_c0ntr0l_VertiKal}")

if __name__ == "__main__":
    app.run(host="0.0.0.0", port=5000)
