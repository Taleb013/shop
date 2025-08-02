// main.js

// ========== Smooth Scrolling for Navigation Links ==========
document.addEventListener("DOMContentLoaded", function () {
    const navLinks = document.querySelectorAll('.navbar-nav a[href^="#"]');

    navLinks.forEach(link => {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            const targetId = this.getAttribute('href').substring(1);
            const targetElement = document.getElementById(targetId);
            if (targetElement) {
                window.scrollTo({
                    top: targetElement.offsetTop - 70,
                    behavior: 'smooth'
                });
            }
        });
    });
});

// ========== Scrollspy-like Active Link Highlighting ==========
window.addEventListener("scroll", function () {
    const sections = document.querySelectorAll("section");
    const navLinks = document.querySelectorAll(".navbar-nav .nav-link");

    let scrollPos = window.scrollY + 80;

    sections.forEach(section => {
        if (
            scrollPos > section.offsetTop &&
            scrollPos < section.offsetTop + section.offsetHeight
        ) {
            navLinks.forEach(link => {
                link.classList.remove("active");
                if (link.getAttribute("href").substring(1) === section.getAttribute("id")) {
                    link.classList.add("active");
                }
            });
        }
    });
});

// ========== Placeholder for Login Modal Logic ==========
function showLoginModal() {
    $('#loginModal').modal('show');
}

function showRegisterModal() {
    $('#registerModal').modal('show');
}

// Example usage if buttons were dynamic
document.addEventListener("DOMContentLoaded", function () {
    const loginButton = document.querySelector('.login-btn');
    const registerButton = document.querySelector('.register-btn');

    if (loginButton) {
        loginButton.addEventListener("click", showLoginModal);
    }

    if (registerButton) {
        registerButton.addEventListener("click", showRegisterModal);
    }
});

// ========== Placeholder for Category Filtering ==========
function filterProductsByCategory(category) {
    // This is where you'd fetch from a backend or filter visible cards
    console.log(`Filtering products by category: ${category}`);

    // Example logic to hide other categories (for future use)
    const allSections = ['clothing', 'books', 'software', 'courses'];
    allSections.forEach(id => {
        const section = document.getElementById(id);
        if (section) {
            section.style.display = (id === category) ? 'block' : 'none';
        }
    });
}

// ========== Scroll to Top Button (optional) ==========
const scrollTopBtn = document.createElement('button');
scrollTopBtn.innerText = '↑';
scrollTopBtn.className = 'scroll-top-btn';
scrollTopBtn.style.position = 'fixed';
scrollTopBtn.style.bottom = '20px';
scrollTopBtn.style.right = '20px';
scrollTopBtn.style.padding = '10px 15px';
scrollTopBtn.style.fontSize = '1.2rem';
scrollTopBtn.style.display = 'none';
scrollTopBtn.style.zIndex = '1000';
scrollTopBtn.style.backgroundColor = '#007bff';
scrollTopBtn.style.color = 'white';
scrollTopBtn.style.border = 'none';
scrollTopBtn.style.borderRadius = '50%';
scrollTopBtn.style.cursor = 'pointer';
document.body.appendChild(scrollTopBtn);

// Show/hide button on scroll
window.addEventListener('scroll', function () {
    if (window.scrollY > 500) {
        scrollTopBtn.style.display = 'block';
    } else {
        scrollTopBtn.style.display = 'none';
    }
});

// Scroll to top on click
scrollTopBtn.addEventListener('click', function () {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
});
// ========== Sample Product Data ==========
const productData = {
    clothing: [
        { title: "Stylish T-Shirt", price: 350, img: "https://via.placeholder.com/400x300?text=T-Shirt" },
        { title: "Casual Jeans", price: 1200, img: "https://via.placeholder.com/400x300?text=Jeans" },
        { title: "Formal Shirt", price: 800, img: "https://via.placeholder.com/400x300?text=Shirt" }
    ],
    books: [
        { title: "Learn HTML & CSS", price: 450, img: "https://via.placeholder.com/400x300?text=HTML+Book" },
        { title: "Mastering Python", price: 650, img: "https://via.placeholder.com/400x300?text=Python+Book" },
        { title: "Data Structures", price: 500, img: "https://via.placeholder.com/400x300?text=DS+Book" }
    ],
    software: [
        { title: "Antivirus Pro", price: 1500, img: "https://via.placeholder.com/400x300?text=Antivirus" },
        { title: "Photo Editor X", price: 1200, img: "https://via.placeholder.com/400x300?text=Editor" },
        { title: "Office Suite", price: 2000, img: "https://via.placeholder.com/400x300?text=Office" }
    ],
    courses: [
        { title: "Web Dev Bootcamp", price: 2500, img: "https://via.placeholder.com/400x300?text=Web+Dev" },
        { title: "Machine Learning A-Z", price: 3000, img: "https://via.placeholder.com/400x300?text=ML+Course" },
        { title: "Digital Marketing", price: 1800, img: "https://via.placeholder.com/400x300?text=Marketing" }
    ]
};

// ========== Render Products Dynamically ==========
function renderProducts(categoryId, data) {
    const section = document.getElementById(categoryId);
    if (!section) return;

    const row = section.querySelector(".row");
    if (!row) return;

    row.innerHTML = ""; // Clear existing items

    if (data.length === 0) {
        row.innerHTML = `<div class="col-12 text-center text-muted">No products found.</div>`;
        return;
    }

    data.forEach(product => {
        const col = document.createElement("div");
        col.className = "col-md-4 mb-4";

        col.innerHTML = `
            <div class="card h-100">
                <img src="${product.img}" class="card-img-top" alt="${product.title}">
                <div class="card-body text-center">
                    <h5 class="card-title">${product.title}</h5>
                    <p class="card-text">Price: ৳${product.price}</p>
                    <a href="#" class="btn btn-primary">Buy Now</a>
                </div>
            </div>
        `;
        row.appendChild(col);
    });
}

// ========== Initialize Product Rendering ==========
document.addEventListener("DOMContentLoaded", function () {
    renderProducts("clothing", productData.clothing);
    renderProducts("books", productData.books);
    renderProducts("software", productData.software);
    renderProducts("courses", productData.courses);
});
// ========== Cart System using localStorage ==========
let cart = [];

// Load cart from localStorage on startup
function loadCart() {
    const storedCart = localStorage.getItem("shoppingCart");
    if (storedCart) {
        cart = JSON.parse(storedCart);
    }
}

// Save cart to localStorage
function saveCart() {
    localStorage.setItem("shoppingCart", JSON.stringify(cart));
}

// Add product to cart
function addToCart(product) {
    const existing = cart.find(item => item.title === product.title);
    if (existing) {
        existing.qty += 1;
    } else {
        cart.push({ ...product, qty: 1 });
    }
    saveCart();
    updateCartBadge();
    alert(`Added "${product.title}" to cart.`);
}

// Display cart count badge (optional)
function updateCartBadge() {
    let count = 0;
    cart.forEach(item => {
        count += item.qty;
    });

    let badge = document.querySelector("#cart-count");
    if (!badge) {
        const nav = document.querySelector(".navbar-nav");
        const li = document.createElement("li");
        li.className = "nav-item";
        li.innerHTML = `
            <a class="nav-link" href="cart.html">
                Cart <span id="cart-count" class="badge badge-pill badge-danger ml-1">${count}</span>
            </a>
        `;
        nav.appendChild(li);
    } else {
        badge.textContent = count;
    }
}

// Attach Add-to-Cart buttons after products are rendered
function setupAddToCartButtons() {
    document.querySelectorAll(".card .btn").forEach(btn => {
        btn.addEventListener("click", function (e) {
            e.preventDefault();
            const card = this.closest(".card");
            const title = card.querySelector(".card-title").innerText;
            const priceText = card.querySelector(".card-text").innerText;
            const price = parseFloat(priceText.replace(/[^\d.]/g, ""));
            const img = card.querySelector("img").src;

            addToCart({ title, price, img });
        });
    });
}

// Hook setup into product rendering
document.addEventListener("DOMContentLoaded", function () {
    loadCart();
    updateCartBadge();

    // Wait a moment for product rendering to finish
    setTimeout(setupAddToCartButtons, 500);
});
// ========== Render Cart Page ==========
function renderCartPage() {
    if (!window.location.href.includes("cart.html")) return;

    const container = document.getElementById("cart-container");
    if (!container) return;

    if (cart.length === 0) {
        container.innerHTML = `<div class="alert alert-info text-center">Your cart is empty. <a href="index.html">Continue Shopping</a></div>`;
        return;
    }

    let totalQty = 0;
    let totalPrice = 0;

    const table = document.createElement("table");
    table.className = "table table-bordered table-hover text-center";
    const thead = `
        <thead class="thead-dark">
            <tr>
                <th>#</th>
                <th>Product</th>
                <th>Image</th>
                <th>Qty</th>
                <th>Price</th>
                <th>Total</th>
                <th>Action</th>
            </tr>
        </thead>
    `;
    table.innerHTML = thead;

    const tbody = document.createElement("tbody");

    cart.forEach((item, index) => {
        const itemTotal = item.price * item.qty;
        totalQty += item.qty;
        totalPrice += itemTotal;

        const tr = document.createElement("tr");
        tr.innerHTML = `
            <td>${index + 1}</td>
            <td>${item.title}</td>
            <td><img src="${item.img}" width="60" alt="${item.title}"></td>
            <td>${item.qty}</td>
            <td>৳${item.price.toFixed(2)}</td>
            <td>৳${itemTotal.toFixed(2)}</td>
            <td>
                <button class="btn btn-sm btn-danger remove-btn" data-title="${item.title}">Remove</button>
            </td>
        `;
        tbody.appendChild(tr);
    });

    table.appendChild(tbody);
    container.innerHTML = "";
    container.appendChild(table);

    const summary = document.createElement("div");
    summary.className = "text-right mt-4";
    summary.innerHTML = `
        <h5>Total Quantity: ${totalQty}</h5>
        <h4>Total Price: ৳${totalPrice.toFixed(2)}</h4>
        <a href="#" class="btn btn-success mt-2 disabled">Proceed to Checkout</a>
    `;
    container.appendChild(summary);

    // Bind remove buttons
    document.querySelectorAll(".remove-btn").forEach(button => {
        button.addEventListener("click", function () {
            const title = this.dataset.title;
            removeItemFromCart(title);
            renderCartPage();
            updateCartBadge();
        });
    });
}

// ========== Remove Item ==========
function removeItemFromCart(title) {
    cart = cart.filter(item => item.title !== title);
    saveCart();
}
// Save product from form to localStorage
document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("productUploadForm");
  if (form) {
    form.addEventListener("submit", function (e) {
      e.preventDefault();

      const title = document.getElementById("productTitle").value.trim();
      const category = document.getElementById("productCategory").value;
      const price = document.getElementById("productPrice").value;
      const image = document.getElementById("productImage").value.trim();
      const description = document.getElementById("productDescription").value.trim();

      const product = { id: Date.now(), title, category, price, image, description };

      let products = JSON.parse(localStorage.getItem("products")) || [];
      products.push(product);
      localStorage.setItem("products", JSON.stringify(products));

      document.getElementById("uploadSuccess").classList.remove("d-none");
      form.reset();
      setTimeout(() => {
        document.getElementById("uploadSuccess").classList.add("d-none");
      }, 2000);
    });
  }

  // Load products in admin-products.html
  const productList = document.getElementById("productList");
  if (productList) {
    const products = JSON.parse(localStorage.getItem("products")) || [];

    if (products.length === 0) {
      productList.innerHTML = `<div class="col text-center"><p>No products found.</p></div>`;
    } else {
      productList.innerHTML = "";
      products.forEach(product => {
        const card = document.createElement("div");
        card.className = "col-md-4 mb-4";
        card.innerHTML = `
          <div class="card h-100 shadow-sm">
            <img src="${product.image}" class="card-img-top" alt="Product Image">
            <div class="card-body">
              <h5 class="card-title">${product.title}</h5>
              <p class="card-text">${product.description}</p>
              <p class="card-text"><strong>Category:</strong> ${product.category}</p>
              <p class="card-text"><strong>Price:</strong> $${product.price}</p>
            </div>
            <div class="card-footer text-end">
              <button class="btn btn-sm btn-danger delete-product" data-id="${product.id}">Delete</button>
            </div>
          </div>
        `;
        productList.appendChild(card);
      });
    }

    // Handle delete buttons
    productList.addEventListener("click", function (e) {
      if (e.target.classList.contains("delete-product")) {
        const id = e.target.getAttribute("data-id");
        let products = JSON.parse(localStorage.getItem("products")) || [];
        products = products.filter(p => p.id != id);
        localStorage.setItem("products", JSON.stringify(products));
        location.reload();
      }
    });
  }
});
// ========== CART PAGE LOGIC ==========
document.addEventListener("DOMContentLoaded", () => {
  const cartItemsContainer = document.getElementById("cartItems");
  const cartTotalElement = document.getElementById("cartTotal");
  const checkoutBtn = document.getElementById("checkoutBtn");

  if (cartItemsContainer && cartTotalElement) {
    let cart = JSON.parse(localStorage.getItem("cart")) || [];

    const renderCart = () => {
      cartItemsContainer.innerHTML = "";
      let total = 0;

      if (cart.length === 0) {
        cartItemsContainer.innerHTML = `
          <div class="col text-center">
            <h5>Your cart is empty.</h5>
            <a href="index.php" class="btn btn-outline-primary mt-3">Go Shopping</a>
          </div>`;
        cartTotalElement.textContent = "$0.00";
        return;
      }

      cart.forEach((item, index) => {
        total += parseFloat(item.price);

        const itemCard = document.createElement("div");
        itemCard.className = "col-md-4";
        itemCard.innerHTML = `
          <div class="card h-100 shadow-sm">
            <img src="${item.image}" class="card-img-top" style="height: 200px; object-fit: cover;" alt="${item.title}">
            <div class="card-body">
              <h5 class="card-title">${item.title}</h5>
              <p class="card-text">${item.description || "No description available."}</p>
              <p><strong>Price:</strong> $${item.price}</p>
              <button class="btn btn-sm btn-danger remove-item" data-index="${index}">Remove</button>
            </div>
          </div>`;
        cartItemsContainer.appendChild(itemCard);
      });

      cartTotalElement.textContent = `$${total.toFixed(2)}`;
    };

    renderCart();

    cartItemsContainer.addEventListener("click", (e) => {
      if (e.target.classList.contains("remove-item")) {
        const index = e.target.getAttribute("data-index");
        cart.splice(index, 1);
        localStorage.setItem("cart", JSON.stringify(cart));
        renderCart();
      }
    });

    if (checkoutBtn) {
      checkoutBtn.addEventListener("click", () => {
        if (cart.length > 0) {
          alert("Thank you for your purchase!");
          localStorage.removeItem("cart");
          renderCart();
        }
      });
    }
  }
});
