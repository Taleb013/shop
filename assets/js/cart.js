// assets/js/cart.js

document.addEventListener("DOMContentLoaded", function () {
    // Handle quantity increase
    document.querySelectorAll(".qty-plus").forEach(button => {
        button.addEventListener("click", function () {
            let qtyInput = this.parentElement.querySelector(".qty-input");
            qtyInput.value = parseInt(qtyInput.value) + 1;
            updateCart(this.dataset.id, qtyInput.value);
        });
    });

    // Handle quantity decrease
    document.querySelectorAll(".qty-minus").forEach(button => {
        button.addEventListener("click", function () {
            let qtyInput = this.parentElement.querySelector(".qty-input");
            if (parseInt(qtyInput.value) > 1) {
                qtyInput.value = parseInt(qtyInput.value) - 1;
                updateCart(this.dataset.id, qtyInput.value);
            }
        });
    });

    // Handle remove button
    document.querySelectorAll(".remove-btn").forEach(button => {
        button.addEventListener("click", function () {
            if (confirm("Remove this item from the cart?")) {
                removeFromCart(this.dataset.id);
            }
        });
    });

    // AJAX call to update quantity in backend
    function updateCart(productId, quantity) {
        fetch("update_cart.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded"
            },
            body: `id=${productId}&quantity=${quantity}`
        })
        .then(res => res.text())
        .then(data => {
            console.log(data);
            location.reload(); // Reload page to update totals
        })
        .catch(err => console.error(err));
    }

    // AJAX call to remove item from cart
    function removeFromCart(productId) {
        fetch("remove_cart.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded"
            },
            body: `id=${productId}`
        })
        .then(res => res.text())
        .then(data => {
            console.log(data);
            location.reload();
        })
        .catch(err => console.error(err));
    }
});
