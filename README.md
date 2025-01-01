# Jakach Login

**The new (future) open-source login system for Jakach**

Jakach Login offers an easy and secure way to access all Jakach services, including **jakach.ch**, **app.ksw3d.ch**, and more. It simplifies the login process by enabling users to log in with passwords, passkeys, and two-factor authentication (2FA) for enhanced security.

Jakach Login operates on its own authentication server. Any app utilizing Jakach Login must be able to communicate with this server via API requests.

---

## Usage

Using Jakach Login is straightforward:  

1. **Clone the repository:**  
   ```bash
   git clone https://github.com/jakani24/jakach-login
   ```
2. **Create the `certs/` folder and set up SSL certificates:**  
   ```bash
   mkdir certs/
   ```
   - Generate certificates (e.g., using [Let's Encrypt](https://letsencrypt.org/getting-started/#with-shell-access)).

3. **Create a Docker volume for database storage:**  
   ```bash
   docker volume create jakach-login-db-storage
   ```
4. **Run the system using Docker Compose:**  
   ```bash
   docker-compose up
   ```

---

## Integrating Jakach Login into Your App

To integrate Jakach Login into your application:  

1. **Refer to the sample authentication plugin:**  
   - Locate the file `/app-code/plugins/auth.php`. This file serves as an example for adding Jakach Login to your app.
   - Modify it to include your database connection and any necessary logic to load service-specific data after authentication.

2. **Account for dynamic usernames:**  
   - Note that the "username" provided by Jakach Login may change over time.
   - The "user_token" is immutable. It's recommended to add a `user_token` column in your service's database and use it for data retrieval.

3. **Example SQL query:**  
   After a user is authenticated through Jakach Login, you can retrieve their data in your own local db using a statement like:  
   ```sql
   SELECT * FROM users WHERE user_token = {user token from Jakach Login server};
   ```
4. **Add login button:**  
   In your app/login page add a button like:  
   ```html
   <a href="https://auth.jakach.ch/?send_to=<your url/place of your oauth file>" class="btn btn-secondary">Log in using Jakach login</a>
   ```

---

Jakach Login is used at [system0](https://github.com/jakani24/system0) You can also check the implementation as an example. [system0/oauth](https://github.com/jakani24/system0-2.0/blob/main/sys0-code/login/oauth.php)
For more details, explore the repository at [GitHub - Jakach Login](https://github.com/jakani24/jakach-login).
