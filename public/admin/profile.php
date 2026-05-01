<?php session_start(); ?>
<?php
include('./includes/header.php');
include('./includes/topbar.php');
include('./includes/sidebar.php');
require_once('../../app/controllers/profileController.php');

// Fetch user from DB using session id
$userId = isset($_SESSION['authUser']['user_id']) ? $_SESSION['authUser']['user_id'] : 0;
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    echo "<script>window.location.href='../login.php';</script>";
    exit();
}

$fullName   = trim(
    (isset($user['firstName']) ? $user['firstName'] : '') . ' ' .
        (isset($user['middleName']) ? $user['middleName'] : '') . ' ' .
        (isset($user['lastName']) ? $user['lastName'] : '')
);
$initials   = strtoupper(
    substr(isset($user['firstName']) ? $user['firstName'] : 'U', 0, 1) .
        substr(isset($user['lastName']) ? $user['lastName'] : 'U', 0, 1)
);
$address    = trim(
    (isset($user['street']) ? $user['street'] : '') . ', ' .
        (isset($user['barangay']) ? $user['barangay'] : '') . ', ' .
        (isset($user['city']) ? $user['city'] : '')
);
$dateJoined = (!empty($user['dateCreated'])) ? date('F j, Y', strtotime($user['dateCreated'])) : 'N/A';
?>

<style>
    .profile-wrapper {
        max-width: 720px;
        margin: 1.5rem auto;
        font-family: 'DM Sans', sans-serif;
    }

    .profile-hero {
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
        border-radius: 20px;
        padding: 2.5rem 2rem 4rem;
    }

    .profile-hero-inner {
        display: flex;
        align-items: center;
        gap: 1.5rem;
    }

    .profile-avatar {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.2);
        border: 3px solid rgba(255, 255, 255, 0.4);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
        font-weight: 700;
        color: #fff;
        flex-shrink: 0;
    }

    .profile-hero-name {
        font-size: 1.5rem;
        font-weight: 700;
        color: #fff;
        letter-spacing: -0.02em;
        margin: 0;
    }

    .profile-hero-role {
        font-size: 0.75rem;
        color: rgba(255, 255, 255, 0.75);
        margin-top: 4px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.08em;
    }

    .profile-body {
        background: #fff;
        border: 1px solid #eaecf4;
        border-radius: 20px;
        margin-top: -2rem;
        padding: 2.5rem 2rem 2rem;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
    }

    .profile-section-title {
        font-size: 0.65rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.12em;
        color: #9ca3af;
        margin-bottom: 1rem;
        padding-bottom: 0.5rem;
        border-bottom: 1px solid #eaecf4;
    }

    .profile-info-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
        margin-bottom: 1.75rem;
    }

    @media (max-width: 540px) {
        .profile-info-grid {
            grid-template-columns: 1fr;
        }
    }

    .profile-info-item {
        background: #f5f7fb;
        border-radius: 12px;
        padding: 0.85rem 1rem;
    }

    .profile-info-item.full {
        grid-column: 1 / -1;
    }

    .profile-info-label {
        font-size: 0.6rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        color: #9ca3af;
        margin-bottom: 4px;
    }

    .profile-info-value {
        font-size: 0.88rem;
        font-weight: 600;
        color: #111827;
        word-break: break-word;
    }

    .profile-badge {
        display: inline-block;
        background: #dbeafe;
        color: #1d4ed8;
        font-size: 0.7rem;
        font-weight: 700;
        padding: 3px 12px;
        border-radius: 20px;
        letter-spacing: 0.04em;
        text-transform: capitalize;
    }
</style>

<section class="section">
    <div class="profile-wrapper">

        <div class="profile-hero">
            <div class="profile-hero-inner">
                <div class="profile-avatar">
                    <?php if (!empty($user['profilePic'])): ?>
                        <img src="<?php echo htmlspecialchars($user['profilePic']); ?>" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">
                    <?php else: ?>
                        <?php echo htmlspecialchars($initials); ?>
                    <?php endif; ?>
                </div>
                <div>
                    <div class="profile-hero-name"><?php echo htmlspecialchars($fullName); ?></div>
                    <div class="profile-hero-role"><?php echo htmlspecialchars($user['role']); ?></div>
                </div>
                <div style="margin-left:auto;">
                    <button onclick="document.getElementById('editModal').style.display='flex'"
                        style="background:rgba(255,255,255,0.2);border:2px solid rgba(255,255,255,0.5);color:#fff;padding:8px 18px;border-radius:10px;font-weight:600;cursor:pointer;font-size:0.85rem;">
                        Edit Profile
                    </button>
                </div>
            </div>
        </div>
        <div class="profile-body">

            <div class="profile-section-title">Personal Information</div>

            <div class="profile-info-grid">
                <div class="profile-info-item">
                    <div class="profile-info-label">First Name</div>
                    <div class="profile-info-value"><?php echo htmlspecialchars($user['firstName']); ?></div>
                </div>
                <div class="profile-info-item">
                    <div class="profile-info-label">Middle Name</div>
                    <div class="profile-info-value"><?php echo htmlspecialchars($user['middleName'] ? $user['middleName'] : '—'); ?></div>
                </div>
                <div class="profile-info-item">
                    <div class="profile-info-label">Last Name</div>
                    <div class="profile-info-value"><?php echo htmlspecialchars($user['lastName']); ?></div>
                </div>
                <div class="profile-info-item">
                    <div class="profile-info-label">Role</div>
                    <div class="profile-info-value">
                        <span class="profile-badge"><?php echo htmlspecialchars($user['role']); ?></span>
                    </div>
                </div>
            </div>

            <div class="profile-section-title">Account Details</div>
            <div class="profile-info-grid">
                <div class="profile-info-item">
                    <div class="profile-info-label">Username</div>
                    <div class="profile-info-value">@<?php echo htmlspecialchars($user['username']); ?></div>
                </div>
                <div class="profile-info-item">
                    <div class="profile-info-label">Email Address</div>
                    <div class="profile-info-value"><?php echo htmlspecialchars($user['emailAddress']); ?></div>
                </div>
                <div class="profile-info-item">
                    <div class="profile-info-label">Member Since</div>
                    <div class="profile-info-value"><?php echo $dateJoined; ?></div>
                </div>
            </div>

            <div class="profile-section-title">Address</div>
            <div class="profile-info-grid">
                <div class="profile-info-item">
                    <div class="profile-info-label">Street</div>
                    <div class="profile-info-value"><?php echo htmlspecialchars($user['street'] ? $user['street'] : '—'); ?></div>
                </div>
                <div class="profile-info-item">
                    <div class="profile-info-label">Barangay</div>
                    <div class="profile-info-value"><?php echo htmlspecialchars($user['barangay'] ? $user['barangay'] : '—'); ?></div>
                </div>
                <div class="profile-info-item full">
                    <div class="profile-info-label">City</div>
                    <div class="profile-info-value"><?php echo htmlspecialchars($user['city'] ? $user['city'] : '—'); ?></div>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- Edit Profile Modal -->
<div id="editModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:20px;padding:2rem;width:100%;max-width:560px;max-height:90vh;overflow-y:auto;margin:1rem;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;">
            <h3 style="margin:0;font-size:1.1rem;font-weight:700;">Edit Profile</h3>
            <button onclick="document.getElementById('editModal').style.display='none'"
                style="background:none;border:none;font-size:1.4rem;cursor:pointer;color:#6b7280;">&times;</button>
        </div>

        <?php if (!empty($success)): ?>
            <div style="background:#d1fae5;color:#065f46;padding:10px 14px;border-radius:10px;margin-bottom:1rem;font-size:0.85rem;"><?php echo $success; ?></div>
        <?php endif; ?>
        <?php if (!empty($error)): ?>
            <div style="background:#fee2e2;color:#991b1b;padding:10px 14px;border-radius:10px;margin-bottom:1rem;font-size:0.85rem;"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" action="" enctype="multipart/form-data">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div style="grid-column:1/-1;display:flex;align-items:center;gap:1rem;padding:1rem;background:#f5f7fb;border-radius:12px;">
                    <div id="avatarPreview" style="width:70px;height:70px;border-radius:50%;background:#2563eb;display:flex;align-items:center;justify-content:center;font-size:1.5rem;font-weight:700;color:#fff;flex-shrink:0;overflow:hidden;">
                        <?php if (!empty($user['profilePic'])): ?>
                            <img src="<?php echo htmlspecialchars($user['profilePic']); ?>" style="width:100%;height:100%;object-fit:cover;">
                        <?php else: ?>
                            <?php echo $initials; ?>
                        <?php endif; ?>
                    </div>
                    <div style="flex:1;">
                        <div style="font-size:0.82rem;font-weight:700;color:#111827;margin-bottom:4px;">Profile Photo</div>
                        <div style="font-size:0.72rem;color:#6b7280;margin-bottom:8px;">JPG, PNG, GIF or WEBP. Max 2MB.</div>
                        <label style="display:inline-block;padding:6px 14px;background:#2563eb;color:#fff;border-radius:8px;font-size:0.78rem;font-weight:600;cursor:pointer;">
                            Choose Photo
                            <input type="file" name="profilePic" accept="image/*" style="display:none;" onchange="previewPhoto(this)">
                        </label>
                    </div>
                </div>

                <div>
                    <label style="font-size:0.72rem;font-weight:700;color:#6b7280;text-transform:uppercase;">First Name *</label>
                    <input type="text" name="firstName" value="<?php echo htmlspecialchars($user['firstName']); ?>" required
                        style="width:100%;padding:9px 12px;border:1px solid #e5e7eb;border-radius:10px;font-size:0.88rem;margin-top:4px;box-sizing:border-box;">
                </div>
                <div>
                    <label style="font-size:0.72rem;font-weight:700;color:#6b7280;text-transform:uppercase;">Middle Name</label>
                    <input type="text" name="middleName" value="<?php echo htmlspecialchars($user['middleName'] ?? ''); ?>"
                        style="width:100%;padding:9px 12px;border:1px solid #e5e7eb;border-radius:10px;font-size:0.88rem;margin-top:4px;box-sizing:border-box;">
                </div>
                <div>
                    <label style="font-size:0.72rem;font-weight:700;color:#6b7280;text-transform:uppercase;">Last Name *</label>
                    <input type="text" name="lastName" value="<?php echo htmlspecialchars($user['lastName']); ?>" required
                        style="width:100%;padding:9px 12px;border:1px solid #e5e7eb;border-radius:10px;font-size:0.88rem;margin-top:4px;box-sizing:border-box;">
                </div>
                <div>
                    <label style="font-size:0.72rem;font-weight:700;color:#6b7280;text-transform:uppercase;">Username *</label>
                    <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required
                        style="width:100%;padding:9px 12px;border:1px solid #e5e7eb;border-radius:10px;font-size:0.88rem;margin-top:4px;box-sizing:border-box;">
                </div>
                <div style="grid-column:1/-1;">
                    <label style="font-size:0.72rem;font-weight:700;color:#6b7280;text-transform:uppercase;">Email Address *</label>
                    <input type="email" name="emailAddress" value="<?php echo htmlspecialchars($user['emailAddress']); ?>" required
                        style="width:100%;padding:9px 12px;border:1px solid #e5e7eb;border-radius:10px;font-size:0.88rem;margin-top:4px;box-sizing:border-box;">
                </div>
                <div>
                    <label style="font-size:0.72rem;font-weight:700;color:#6b7280;text-transform:uppercase;">Street</label>
                    <input type="text" name="street" value="<?php echo htmlspecialchars($user['street'] ?? ''); ?>"
                        style="width:100%;padding:9px 12px;border:1px solid #e5e7eb;border-radius:10px;font-size:0.88rem;margin-top:4px;box-sizing:border-box;">
                </div>
                <div>
                    <label style="font-size:0.72rem;font-weight:700;color:#6b7280;text-transform:uppercase;">Barangay</label>
                    <input type="text" name="barangay" value="<?php echo htmlspecialchars($user['barangay'] ?? ''); ?>"
                        style="width:100%;padding:9px 12px;border:1px solid #e5e7eb;border-radius:10px;font-size:0.88rem;margin-top:4px;box-sizing:border-box;">
                </div>
                <div style="grid-column:1/-1;">
                    <label style="font-size:0.72rem;font-weight:700;color:#6b7280;text-transform:uppercase;">City</label>
                    <input type="text" name="city" value="<?php echo htmlspecialchars($user['city'] ?? ''); ?>"
                        style="width:100%;padding:9px 12px;border:1px solid #e5e7eb;border-radius:10px;font-size:0.88rem;margin-top:4px;box-sizing:border-box;">
                </div>
                <div>
                    <label style="font-size:0.72rem;font-weight:700;color:#6b7280;text-transform:uppercase;">New Password</label>
                    <input type="password" name="newPassword"
                        style="width:100%;padding:9px 12px;border:1px solid #e5e7eb;border-radius:10px;font-size:0.88rem;margin-top:4px;box-sizing:border-box;">
                </div>
                <div>
                    <label style="font-size:0.72rem;font-weight:700;color:#6b7280;text-transform:uppercase;">Confirm Password</label>
                    <input type="password" name="confirmPassword"
                        style="width:100%;padding:9px 12px;border:1px solid #e5e7eb;border-radius:10px;font-size:0.88rem;margin-top:4px;box-sizing:border-box;">
                </div>
            </div>
            <div style="margin-top:1.5rem;display:flex;gap:10px;justify-content:flex-end;">
                <button type="button" onclick="document.getElementById('editModal').style.display='none'"
                    style="padding:9px 20px;border:1px solid #e5e7eb;border-radius:10px;background:#fff;font-weight:600;cursor:pointer;font-size:0.88rem;">
                    Cancel
                </button>
                <button type="submit"
                    style="padding:9px 20px;border:none;border-radius:10px;background:#2563eb;color:#fff;font-weight:600;cursor:pointer;font-size:0.88rem;">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function previewPhoto(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('avatarPreview').innerHTML =
                    '<img src="' + e.target.result + '" style="width:100%;height:100%;object-fit:cover;">';
            };
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>

<!-- Auto-open modal if there's an error/success after submit -->
<?php if (!empty($error) || !empty($success)): ?>
    <script>
        document.getElementById('editModal').style.display = 'flex';
    </script>
<?php endif; ?>

<?php include('./includes/footer.php'); ?>