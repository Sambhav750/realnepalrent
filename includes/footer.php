    </main>
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>NepalRent</h3>
                    <p>Online Car Rental System for Nepal</p>
                </div>
                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="index.php#cars">Browse Cars</a></li>
                        <?php if (isset($_SESSION['CustomerID'])): ?>
                            <li><a href="dashboard.php">Dashboard</a></li>
                        <?php else: ?>
                            <li><a href="login.php">Login</a></li>
                            <li><a href="register.php">Register</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Contact</h4>
                    <p>info@nepalrent.com</p>
                    <p>+977-98XXXXXXXX</p>
                    <p>Kathmandu, Nepal</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> NepalRent. All rights reserved. | BCA Project</p>
            </div>
        </div>
    </footer>

    <script src="assets/js/script.js"></script>
</body>
</html>