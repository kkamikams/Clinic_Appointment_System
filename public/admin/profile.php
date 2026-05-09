<?php session_start(); ?>
<?php
include('./includes/header.php');
include('./includes/topbar.php');
include('./includes/sidebar.php');
require_once('../../app/controllers/profileController.php');

$flashSuccess = '';
if (!empty($_SESSION['profile_success'])) {
    $flashSuccess = $_SESSION['profile_success'];
    unset($_SESSION['profile_success']);
}

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

$fullName = trim(
    (isset($user['firstName']) ? $user['firstName'] : '') . ' ' .
    (isset($user['middleName']) ? $user['middleName'] : '') . ' ' .
    (isset($user['lastName']) ? $user['lastName'] : '')
);
$initials = strtoupper(
    substr(isset($user['firstName']) ? $user['firstName'] : 'U', 0, 1) .
    substr(isset($user['lastName']) ? $user['lastName'] : 'U', 0, 1)
);
$dateJoined = (!empty($user['dateCreated'])) ? date('F j, Y', strtotime($user['dateCreated'])) : 'N/A';
?>

<style>
    .profile-wrapper,
    .profile-wrapper *,
    #editModal,
    #editModal * {
        font-family: 'DM Sans', sans-serif;
        box-sizing: border-box;
    }

    .profile-wrapper { max-width: 720px; margin: 1.5rem auto; }

    .profile-hero {
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
        border-radius: 20px;
        padding: 2.5rem 2rem 4rem;
    }
    .profile-hero-inner { display: flex; align-items: center; gap: 1.5rem; }
    .profile-avatar {
        width: 80px; height: 80px; border-radius: 50%;
        background: rgba(255,255,255,.2);
        border: 3px solid rgba(255,255,255,.4);
        display: flex; align-items: center; justify-content: center;
        font-size: 1.8rem; font-weight: 700; color: #fff; flex-shrink: 0;
    }
    .profile-hero-name  { font-size: 1.5rem; font-weight: 700; color: #fff; letter-spacing: -.02em; margin: 0; }
    .profile-hero-role  { font-size: .75rem; color: rgba(255,255,255,.75); margin-top: 4px; font-weight: 600; text-transform: uppercase; letter-spacing: .08em; }

    .profile-body {
        background: #fff; border: 1px solid #eaecf4; border-radius: 20px;
        margin-top: -2rem; padding: 2.5rem 2rem 2rem;
        box-shadow: 0 4px 20px rgba(0,0,0,.06);
    }
    .profile-section-title {
        font-size: .65rem; font-weight: 700; text-transform: uppercase;
        letter-spacing: .12em; color: #9ca3af;
        margin-bottom: 1rem; padding-bottom: .5rem; border-bottom: 1px solid #eaecf4;
    }
    .profile-info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 1.75rem; }
    @media (max-width: 540px) { .profile-info-grid { grid-template-columns: 1fr; } }
    .profile-info-item { background: #f5f7fb; border-radius: 12px; padding: .85rem 1rem; }
    .profile-info-item.full { grid-column: 1/-1; }
    .profile-info-label { font-size: .6rem; font-weight: 700; text-transform: uppercase; letter-spacing: .1em; color: #9ca3af; margin-bottom: 4px; }
    .profile-info-value { font-size: .88rem; font-weight: 600; color: #111827; word-break: break-word; }
    .profile-badge { display: inline-block; background: #dbeafe; color: #1d4ed8; font-size: .7rem; font-weight: 700; padding: 3px 12px; border-radius: 20px; letter-spacing: .04em; text-transform: capitalize; }

    /* ── Field validation styles ── */
    .m-field-wrap { display: flex; flex-direction: column; gap: 4px; }
    .m-field-wrap.field-error input,
    .m-field-wrap.field-error select {
        border-color: #ef4444 !important;
        background: #fff8f8 !important;
        box-shadow: 0 0 0 2px rgba(239,68,68,.10);
    }
    .m-field-err {
        font-size: .68rem;
        color: #ef4444;
        font-weight: 500;
        display: none;
        margin-top: 1px;
    }
    .m-field-wrap.field-error .m-field-err { display: block; }

    /* ── Green success banner ── */
    .modal-banner-success {
        display: none;
        position: sticky;
        bottom: 0;
        align-items: center;
        gap: 7px;
        background: #166534;
        color: #fff;
        font-size: .75rem;
        font-weight: 600;
        border-radius: 999px;
        padding: .4rem 1rem;
        box-shadow: 0 2px 10px rgba(22,101,52,.25);
        width: fit-content;
        margin: .6rem 0 0 auto;
        animation: mFadeUp .22s ease both;
    }

    @keyframes mFadeUp {
        from { opacity: 0; transform: translateY(6px); }
        to   { opacity: 1; transform: translateY(0);   }
    }

    .m-input {
        width: 100%;
        padding: 9px 12px;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        font-size: .88rem;
        margin-top: 4px;
        font-family: 'DM Sans', sans-serif;
        transition: border-color .2s, box-shadow .2s, background .2s;
        outline: none;
        box-sizing: border-box;
    }
    .m-input:focus { border-color: #60a5fa; box-shadow: 0 0 0 3px rgba(96,165,250,.15); }
    .m-label {
        font-size: .72rem; font-weight: 700; color: #6b7280;
        text-transform: uppercase; letter-spacing: .05em;
    }
    .m-label .req { color: #ef4444; margin-left: 2px; }
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
                    <button onclick="openEditModal()"
                        style="background:rgba(255,255,255,.2);border:2px solid rgba(255,255,255,.5);color:#fff;padding:8px 18px;border-radius:10px;font-weight:600;cursor:pointer;font-size:.85rem;">
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

<!-- ═══════════════════════════════════════════════════════
     Edit Profile Modal
════════════════════════════════════════════════════════ -->
<div id="editModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:20px;padding:2rem;width:100%;max-width:560px;max-height:90vh;overflow-y:auto;margin:1rem;">

        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;">
            <h3 style="margin:0;font-size:1.1rem;font-weight:700;">Edit Profile</h3>
            <button onclick="closeEditModal()"
                style="background:none;border:none;font-size:1.1rem;cursor:pointer;color:#9ca3af;line-height:1;">✕</button>
        </div>

        <?php if (!empty($error)): ?>
            <div style="background:#fee2e2;color:#991b1b;padding:10px 14px;border-radius:10px;margin-bottom:1rem;font-size:.85rem;">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form id="editForm" method="POST" action="" enctype="multipart/form-data" novalidate>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">

                <!-- Profile photo row -->
                <div style="grid-column:1/-1;display:flex;align-items:center;gap:1rem;padding:1rem;background:#f5f7fb;border-radius:12px;">
                    <div id="avatarPreview" style="width:70px;height:70px;border-radius:50%;background:#2563eb;display:flex;align-items:center;justify-content:center;font-size:1.5rem;font-weight:700;color:#fff;flex-shrink:0;overflow:hidden;">
                        <?php if (!empty($user['profilePic'])): ?>
                            <img src="<?php echo htmlspecialchars($user['profilePic']); ?>" style="width:100%;height:100%;object-fit:cover;">
                        <?php else: ?>
                            <?php echo $initials; ?>
                        <?php endif; ?>
                    </div>
                    <div style="flex:1;">
                        <div style="font-size:.82rem;font-weight:700;color:#111827;margin-bottom:4px;">Profile Photo</div>
                        <div style="font-size:.72rem;color:#6b7280;margin-bottom:8px;">JPG, PNG, GIF or WEBP. Max 2MB.</div>
                        <label style="display:inline-block;padding:6px 14px;background:#2563eb;color:#fff;border-radius:8px;font-size:.78rem;font-weight:600;cursor:pointer;">
                            Choose Photo
                            <input type="file" name="profilePic" accept="image/*" style="display:none;" onchange="previewPhoto(this)">
                        </label>
                    </div>
                </div>

                <!-- First Name -->
                <div>
                    <label class="m-label">First Name <span class="req">*</span></label>
                    <div class="m-field-wrap" id="mwrap-firstName">
                        <input type="text" name="firstName" id="mFirstName" class="m-input"
                            value="<?php echo htmlspecialchars($user['firstName']); ?>"
                            oninput="mClear('mwrap-firstName')">
                        <span class="m-field-err">First name is required.</span>
                    </div>
                </div>

                <!-- Middle Name -->
                <div>
                    <label class="m-label">Middle Name</label>
                    <input type="text" name="middleName" class="m-input"
                        value="<?php echo htmlspecialchars($user['middleName'] ?? ''); ?>">
                </div>

                <!-- Last Name -->
                <div>
                    <label class="m-label">Last Name <span class="req">*</span></label>
                    <div class="m-field-wrap" id="mwrap-lastName">
                        <input type="text" name="lastName" id="mLastName" class="m-input"
                            value="<?php echo htmlspecialchars($user['lastName']); ?>"
                            oninput="mClear('mwrap-lastName')">
                        <span class="m-field-err">Last name is required.</span>
                    </div>
                </div>

                <!-- Username -->
                <div>
                    <label class="m-label">Username <span class="req">*</span></label>
                    <div class="m-field-wrap" id="mwrap-username">
                        <input type="text" name="username" id="mUsername" class="m-input"
                            value="<?php echo htmlspecialchars($user['username']); ?>"
                            oninput="mClear('mwrap-username')">
                        <span class="m-field-err">Username is required.</span>
                    </div>
                </div>

                <!-- Email -->
                <div style="grid-column:1/-1;">
                    <label class="m-label">Email Address <span class="req">*</span></label>
                    <div class="m-field-wrap" id="mwrap-email">
                        <input type="email" name="emailAddress" id="mEmail" class="m-input"
                            value="<?php echo htmlspecialchars($user['emailAddress']); ?>"
                            oninput="mClear('mwrap-email')">
                        <span class="m-field-err">Email address is required.</span>
                    </div>
                </div>

                <!-- Street -->
                <div>
                    <label class="m-label">Street</label>
                    <input type="text" name="street" class="m-input"
                        value="<?php echo htmlspecialchars($user['street'] ?? ''); ?>">
                </div>

                <!-- Barangay -->
                <div>
                    <label class="m-label">Barangay</label>
                    <input type="text" name="barangay" class="m-input"
                        value="<?php echo htmlspecialchars($user['barangay'] ?? ''); ?>">
                </div>

                <!-- City -->
                <div style="grid-column:1/-1;">
                    <label class="m-label">City</label>
                    <input type="text" name="city" class="m-input"
                        value="<?php echo htmlspecialchars($user['city'] ?? ''); ?>">
                </div>

                <!-- New Password -->
                <div>
                    <label class="m-label">New Password</label>
                    <input type="password" name="newPassword" id="mNewPass" class="m-input"
                        value="" autocomplete="new-password"
                        oninput="mClear('mwrap-confirmPass')">
                </div>

                <!-- Confirm Password -->
                <div>
                    <label class="m-label">Confirm Password</label>
                    <div class="m-field-wrap" id="mwrap-confirmPass">
                        <input type="password" name="confirmPassword" id="mConfirmPass" class="m-input"
                            oninput="mClear('mwrap-confirmPass')">
                        <span class="m-field-err" id="mConfirmPassErr">Passwords do not match.</span>
                    </div>
                </div>

            </div><!-- /grid -->

            <!-- Modal footer -->
            <div style="margin-top:1.5rem;">
                <div style="display:flex;gap:10px;justify-content:flex-end;">
                    <button type="button" onclick="closeEditModal()"
                        style="padding:9px 20px;border:1px solid #e5e7eb;border-radius:10px;background:#fff;font-weight:600;cursor:pointer;font-size:.88rem;">
                        Cancel
                    </button>
                    <button type="button" onclick="submitEditForm()"
                        style="padding:9px 20px;border:none;border-radius:10px;background:#2563eb;color:#fff;font-weight:600;cursor:pointer;font-size:.88rem;">
                        Save Changes
                    </button>
                </div>
            </div>

        </form>

        <!-- Green success banner -->
        <div class="modal-banner-success" id="modalBannerSuccess">
            ✓ Changes saved successfully!
        </div>

    </div>
</div>

<script>
    function openEditModal() {
        document.getElementById('editModal').style.display = 'flex';
    }

    function closeEditModal() {
        document.getElementById('editModal').style.display = 'none';
        document.querySelectorAll('.m-field-wrap').forEach(w => w.classList.remove('field-error'));
        document.getElementById('modalBannerSuccess').style.display = 'none';
    }

    function mClear(wrapperId) {
        document.getElementById(wrapperId)?.classList.remove('field-error');
    }

    function mMark(wrapperId) {
        document.getElementById(wrapperId)?.classList.add('field-error');
    }

    function submitEditForm() {
        const firstName = document.getElementById('mFirstName').value.trim();
        const lastName  = document.getElementById('mLastName').value.trim();
        const username  = document.getElementById('mUsername').value.trim();
        const email     = document.getElementById('mEmail').value.trim();
        const newPass   = document.getElementById('mNewPass').value;
        const confPass  = document.getElementById('mConfirmPass').value;

        let hasError = false;

        if (!firstName) { mMark('mwrap-firstName'); hasError = true; }
        if (!lastName)  { mMark('mwrap-lastName');  hasError = true; }
        if (!username)  { mMark('mwrap-username');  hasError = true; }
        if (!email)     { mMark('mwrap-email');     hasError = true; }

        if (newPass && newPass !== confPass) {
            document.getElementById('mConfirmPassErr').textContent = 'Passwords do not match.';
            mMark('mwrap-confirmPass');
            hasError = true;
        }

        if (hasError) {
            const firstErr = document.querySelector('#editForm .field-error');
            if (firstErr) firstErr.scrollIntoView({ behavior: 'smooth', block: 'center' });
            return;
        }

        document.getElementById('editForm').submit();
    }

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

<?php if (!empty($error)): ?>
<script>
    document.getElementById('editModal').style.display = 'flex';
</script>
<?php endif; ?>

<?php if (!empty($flashSuccess)): ?>
<script>
    const modal = document.getElementById('editModal');
    modal.style.display = 'flex';

    const banner = document.getElementById('modalBannerSuccess');
    banner.style.display = 'flex';

    setTimeout(function() {
        modal.style.transition = 'opacity 0.3s ease';
        modal.style.opacity = '0';
        setTimeout(function() {
            modal.style.display = 'none';
            modal.style.opacity = '1';
            modal.style.transition = '';
            banner.style.display = 'none';
        }, 300);
    }, 1500);
</script>
<?php endif; ?>

<?php include('./includes/footer.php'); ?>