// ==============================
// 🌐 ACCOUNT META (LOGO + NAME)
// ==============================
const ACCOUNT_META = {
  bcf: {
    name: "BCF Ni",
    logo: "./assets/img/bcf.png"
  },
  bgr: {
    name: "BGR BC",
    logo: "./assets/img/bgr.png"
  },
  bwd: {
    name: "BWD Ni",
    logo: "./assets/img/bwd.png"
  },
  all: {
    name: "All Accounts",
    logo: "./assets/img/all.png"
  }
};

// ==============================
// ✅ GLOBAL ACCOUNT (SAFE)
// ==============================
window.currentAccount = localStorage.getItem('account') || 'bcf';

document.addEventListener("DOMContentLoaded", function () {

  const dropdown = document.getElementById('accountSwitcher');

  if (dropdown) {
    dropdown.value = window.currentAccount;

    dropdown.addEventListener('change', function () {
      const account = this.value;

      localStorage.setItem('account', account);
      window.currentAccount = account;

      updateAccountUI();

      // 🔥 optional: trigger page refresh if exists
      if (typeof refreshPageData === "function") {
        refreshPageData();
      }
    });
  }

  // 🔥 initial UI update
  updateAccountUI();
});


// ==============================
// 🔥 UPDATE LOGO + NAME ONLY
// ==============================
function updateAccountUI() {
  const meta = ACCOUNT_META[window.currentAccount] || ACCOUNT_META['bcf'];

  const logo = document.getElementById("accountLogo");
  const name = document.getElementById("accountName");

  if (logo) logo.src = meta.logo;
  if (name) name.innerText = meta.name;
}