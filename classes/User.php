<?php
/**
 * User Class — Authentication, Roles, Profile Management
 */
class User
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // ── Register new user ────────────────────────────────────────────────────
    public function register(array $data): array
    {
        // Duplicate checks
        if ($this->db->count('users', 'email = ?', [$data['email']])) {
            return ['success' => false, 'message' => 'Email address already registered.'];
        }
        if ($this->db->count('users', 'username = ?', [$data['username']])) {
            return ['success' => false, 'message' => 'Username already taken.'];
        }

        $roleId = $this->db->fetchColumn("SELECT id FROM roles WHERE slug = 'customer'");
        if (!$roleId) {
            return ['success' => false, 'message' => 'System not fully set up. Please import seed data first.'];
        }
        $verifyToken = bin2hex(random_bytes(32));

        $userId = $this->db->insert('users', [
            'role_id'             => (int)$roleId,
            'username'            => $data['username'],
            'email'               => strtolower(trim($data['email'])),
            'password'            => password_hash($data['password'], PASSWORD_ALGO, ['cost' => PASSWORD_COST]),
            'full_name'           => trim($data['full_name']),
            'phone'               => $data['phone'] ?? null,
            'status'              => 'active',
            'email_verified'      => 0,
            'email_verify_token'  => $verifyToken,
        ]);

        // tier_id: fetchColumn returns false when table is empty — cast to null safely
        $bronzeId = $this->db->fetchColumn("SELECT id FROM customer_tiers WHERE tier_name='Bronze' LIMIT 1");
        $bronzeId = $bronzeId ? (int)$bronzeId : null;

        $nameParts = explode(' ', trim($data['full_name']));
        $firstName = $nameParts[0];
        $lastName  = implode(' ', array_slice($nameParts, 1)) ?: '';

        // Create customer profile
        $this->db->insert('customers', [
            'user_id'    => $userId,
            'first_name' => $firstName,
            'last_name'  => $lastName,
            'phone'      => $data['phone'] ?? null,
            'country'    => 'Ghana',
            'tier_id'    => $bronzeId,
        ]);

        $this->db->audit('register', 'users', $userId);

        return [
            'success'      => true,
            'user_id'      => $userId,
            'verify_token' => $verifyToken,
            'message'      => 'Registration successful.',
        ];
    }

    // ── Login ────────────────────────────────────────────────────────────────
    public function login(string $emailOrUsername, string $password, bool $remember = false): array
    {
        $user = $this->db->fetchOne(
            "SELECT u.*, r.slug AS role_slug, r.permissions FROM users u
             JOIN roles r ON u.role_id = r.id
             WHERE (u.email = ? OR u.username = ?) LIMIT 1",
            [$emailOrUsername, $emailOrUsername]
        );

        if (!$user) {
            return ['success' => false, 'message' => 'Invalid credentials.'];
        }

        // Account lock check
        if ($user['locked_until'] && strtotime($user['locked_until']) > time()) {
            $mins = ceil((strtotime($user['locked_until']) - time()) / 60);
            return ['success' => false, 'message' => "Account locked. Try again in $mins minute(s)."];
        }

        if (!password_verify($password, $user['password'])) {
            $attempts = $user['login_attempts'] + 1;
            $lockedUntil = $attempts >= MAX_LOGIN_ATTEMPTS
                ? date('Y-m-d H:i:s', strtotime('+' . LOCKOUT_MINUTES . ' minutes'))
                : null;
            $this->db->update('users',
                ['login_attempts' => $attempts, 'locked_until' => $lockedUntil],
                'id = ?', [$user['id']]
            );
            $left = MAX_LOGIN_ATTEMPTS - $attempts;
            return ['success' => false, 'message' => "Invalid credentials. $left attempt(s) remaining."];
        }

        if ($user['status'] !== 'active') {
            return ['success' => false, 'message' => 'Your account is ' . $user['status'] . '.'];
        }

        // Successful — reset counters
        $this->db->update('users', [
            'login_attempts' => 0,
            'locked_until'   => null,
            'last_login'     => date('Y-m-d H:i:s'),
            'last_ip'        => $_SERVER['REMOTE_ADDR'] ?? null,
        ], 'id = ?', [$user['id']]);

        // Start session
        session_regenerate_id(true);
        $_SESSION['user_id']    = $user['id'];
        $_SESSION['username']   = $user['username'];
        $_SESSION['full_name']  = $user['full_name'];
        $_SESSION['role']       = $user['role_slug'];
        $_SESSION['email']      = $user['email'];
        $_SESSION['avatar']     = $user['avatar'];
        $_SESSION['logged_in']  = true;
        $_SESSION['login_time'] = time();

        // Customer-specific — null-safe (handles missing customer profile)
        if ($user['role_slug'] === 'customer') {
            $cust = $this->db->fetchOne("SELECT id, loyalty_points FROM customers WHERE user_id = ?", [$user['id']]);
            if (!$cust) {
                $bronzeId = $this->db->fetchColumn("SELECT id FROM customer_tiers WHERE tier_name='Bronze' LIMIT 1");
                $custId   = $this->db->insert('customers', [
                    'user_id'    => $user['id'],
                    'first_name' => explode(' ', $user['full_name'])[0],
                    'last_name'  => implode(' ', array_slice(explode(' ', $user['full_name']), 1)) ?: '',
                    'country'    => 'Ghana',
                    'tier_id'    => $bronzeId ? (int)$bronzeId : null,
                ]);
                $_SESSION['customer_id']    = $custId;
                $_SESSION['loyalty_points'] = 0;
            } else {
                $_SESSION['customer_id']    = (int)$cust['id'];
                $_SESSION['loyalty_points'] = (int)($cust['loyalty_points'] ?? 0);
            }
        }

        // Remember-me cookie
        if ($remember) {
            $token = bin2hex(random_bytes(32));
            $this->db->update('users', ['remember_token' => $token], 'id = ?', [$user['id']]);
            setcookie('remember_token', $token, time() + (86400 * REMEMBER_DAYS), '/', '', false, true);
        }

        $this->db->audit('login', 'users', $user['id']);

        return ['success' => true, 'user' => $user, 'message' => 'Login successful.'];
    }

    // ── Logout ────────────────────────────────────────────────────────────────
    public function logout(): void
    {
        $this->db->audit('logout', 'users', $_SESSION['user_id'] ?? null);
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
        if (isset($_COOKIE['remember_token'])) {
            setcookie('remember_token', '', time() - 3600, '/');
        }
    }

    // ── Forgot password ──────────────────────────────────────────────────────
    public function forgotPassword(string $email): array
    {
        $user = $this->db->fetchOne("SELECT id, full_name FROM users WHERE email = ? AND status = 'active'", [$email]);
        if (!$user) {
            return ['success' => false, 'message' => 'No active account found with that email.'];
        }
        $token   = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+2 hours'));
        $this->db->update('users', [
            'password_reset_token'   => $token,
            'password_reset_expires' => $expires,
        ], 'id = ?', [$user['id']]);

        return ['success' => true, 'token' => $token, 'user' => $user, 'message' => 'Reset link generated.'];
    }

    // ── Reset password ───────────────────────────────────────────────────────
    public function resetPassword(string $token, string $newPassword): array
    {
        $user = $this->db->fetchOne(
            "SELECT id FROM users WHERE password_reset_token = ? AND password_reset_expires > NOW()",
            [$token]
        );
        if (!$user) {
            return ['success' => false, 'message' => 'Invalid or expired reset token.'];
        }
        $this->db->update('users', [
            'password'             => password_hash($newPassword, PASSWORD_ALGO, ['cost' => PASSWORD_COST]),
            'password_reset_token'   => null,
            'password_reset_expires' => null,
            'login_attempts'         => 0,
            'locked_until'           => null,
        ], 'id = ?', [$user['id']]);
        $this->db->audit('password_reset', 'users', $user['id']);
        return ['success' => true, 'message' => 'Password reset successfully.'];
    }

    // ── Change password ──────────────────────────────────────────────────────
    public function changePassword(int $userId, string $current, string $newPass): array
    {
        $user = $this->db->fetchOne("SELECT password FROM users WHERE id = ?", [$userId]);
        if (!password_verify($current, $user['password'])) {
            return ['success' => false, 'message' => 'Current password is incorrect.'];
        }
        $this->db->update('users', [
            'password' => password_hash($newPass, PASSWORD_ALGO, ['cost' => PASSWORD_COST])
        ], 'id = ?', [$userId]);
        $this->db->audit('change_password', 'users', $userId);
        return ['success' => true, 'message' => 'Password changed successfully.'];
    }

    // ── Verify email ─────────────────────────────────────────────────────────
    public function verifyEmail(string $token): bool
    {
        $rows = $this->db->update('users',
            ['email_verified' => 1, 'email_verify_token' => null],
            'email_verify_token = ? AND email_verified = 0', [$token]
        );
        return $rows > 0;
    }

    // ── Get by ID ─────────────────────────────────────────────────────────────
    public function getById(int $id): ?array
    {
        return $this->db->fetchOne(
            "SELECT u.*, r.role_name, r.slug AS role_slug, r.permissions
             FROM users u JOIN roles r ON u.role_id = r.id WHERE u.id = ?", [$id]
        );
    }

    // ── Get customer profile ─────────────────────────────────────────────────
    public function getCustomerProfile(int $userId): ?array
    {
        return $this->db->fetchOne(
            "SELECT u.*, c.*, ct.tier_name, ct.badge_color, ct.badge_icon,
                    ct.discount_percentage, ct.free_shipping
             FROM users u
             JOIN customers c ON u.id = c.user_id
             LEFT JOIN customer_tiers ct ON c.tier_id = ct.id
             WHERE u.id = ?", [$userId]
        );
    }

    // ── Update profile ───────────────────────────────────────────────────────
    public function updateProfile(int $userId, array $data): array
    {
        $old = $this->getById($userId);
        $this->db->update('users', [
            'full_name' => $data['full_name'],
            'phone'     => $data['phone'] ?? null,
        ], 'id = ?', [$userId]);

        $custId = $this->db->fetchColumn("SELECT id FROM customers WHERE user_id = ?", [$userId]);
        if ($custId) {
            $this->db->update('customers', [
                'first_name'  => $data['first_name'] ?? explode(' ', $data['full_name'])[0],
                'last_name'   => $data['last_name']  ?? '',
                'phone'       => $data['phone'] ?? null,
                'address'     => $data['address'] ?? null,
                'city'        => $data['city'] ?? null,
                'state'       => $data['state'] ?? null,
                'country'     => $data['country'] ?? 'Ghana',
                'postal_code' => $data['postal_code'] ?? null,
                'gender'      => $data['gender'] ?? null,
                'date_of_birth' => $data['date_of_birth'] ?? null,
            ], 'user_id = ?', [$userId]);
        }
        $this->db->audit('update_profile', 'users', $userId, $old, $data);
        return ['success' => true, 'message' => 'Profile updated successfully.'];
    }

    // ── Update avatar ────────────────────────────────────────────────────────
    public function updateAvatar(int $userId, string $avatarPath): void
    {
        $this->db->update('users', ['avatar' => $avatarPath], 'id = ?', [$userId]);
        $this->db->update('customers', ['avatar' => $avatarPath], 'user_id = ?', [$userId]);
        $_SESSION['avatar'] = $avatarPath;
    }

    // ── Admin: List users ────────────────────────────────────────────────────
    public function getAll(array $filters = [], int $page = 1): array
    {
        $where  = ['1=1'];
        $params = [];
        if (!empty($filters['role']))   { $where[] = 'r.slug = ?'; $params[] = $filters['role']; }
        if (!empty($filters['status'])) { $where[] = 'u.status = ?'; $params[] = $filters['status']; }
        if (!empty($filters['search'])) {
            $where[] = '(u.full_name LIKE ? OR u.email LIKE ? OR u.username LIKE ?)';
            $s = '%' . $filters['search'] . '%';
            array_push($params, $s, $s, $s);
        }
        $sql = "SELECT u.id, u.username, u.email, u.full_name, u.status, u.last_login, u.created_at,
                       r.role_name, r.slug AS role_slug
                FROM users u JOIN roles r ON u.role_id = r.id
                WHERE " . implode(' AND ', $where) . " ORDER BY u.created_at DESC";
        return $this->db->paginate($sql, $params, $page, CUSTOMERS_PER_PAGE);
    }

    // ── Check has-permission ─────────────────────────────────────────────────
    public function hasPermission(int $userId, string $permission): bool
    {
        $perms = $this->db->fetchColumn(
            "SELECT r.permissions FROM users u JOIN roles r ON u.role_id = r.id WHERE u.id = ?", [$userId]
        );
        if (!$perms) return false;
        $arr = json_decode($perms, true);
        return isset($arr['all']) || isset($arr[$permission]);
    }

    // ── Toggle status ────────────────────────────────────────────────────────
    public function toggleStatus(int $userId, string $status): void
    {
        $this->db->update('users', ['status' => $status], 'id = ?', [$userId]);
        $this->db->audit("set_status_$status", 'users', $userId);
    }

    // ── Unread notification count ─────────────────────────────────────────────
    public function unreadNotifications(int $userId): int
    {
        return $this->db->count('notifications', 'user_id = ? AND is_read = 0', [$userId]);
    }

    // ── Auto-login via remember_me cookie ────────────────────────────────────
    public function loginViaRememberToken(string $token): bool
    {
        $user = $this->db->fetchOne(
            "SELECT u.*, r.slug AS role_slug FROM users u
             JOIN roles r ON u.role_id = r.id WHERE u.remember_token = ? AND u.status = 'active'",
            [$token]
        );
        if (!$user) return false;
        session_regenerate_id(true);
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['username']  = $user['username'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['role']      = $user['role_slug'];
        $_SESSION['email']     = $user['email'];
        $_SESSION['avatar']    = $user['avatar'];
        $_SESSION['logged_in'] = true;
        $_SESSION['login_time']= time();
        if ($user['role_slug'] === 'customer') {
            $cust = $this->db->fetchOne("SELECT id, loyalty_points FROM customers WHERE user_id = ?", [$user['id']]);
            $_SESSION['customer_id']    = $cust['id'];
            $_SESSION['loyalty_points'] = $cust['loyalty_points'];
        }
        return true;
    }
}
