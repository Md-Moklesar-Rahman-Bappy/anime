<?php
$page_name = $_GET['page'] ?? 'about';
?>
<div class="static-page">
    <?php if ($page_name === '404'): ?>
        <div class="error-page">
            <h1>404</h1>
            <p>Page not found</p>
            <a href="<?= url() ?>" class="btn btn-primary">Go Home</a>
        </div>
    <?php elseif ($page_name === 'about'): ?>
        <h1>About Anikoto</h1>
        <p>Anikoto is a premier free anime streaming platform that offers high-quality anime with English subtitles or dubbing, all at no cost. Our extensive database ensures you can effortlessly find and enjoy virtually any anime with just a single click.</p>
        <p>We started this site to improve user experience and are committed to keeping our users safe. We encourage all our users to notify us if anything looks suspicious.</p>
        <h2>Why Choose Anikoto?</h2>
        <ul>
            <li><strong>Content Library:</strong> Our extensive database ensures you can find almost everything here.</li>
            <li><strong>Streaming Experience:</strong> We have top of the line streaming servers. You can simply choose one that is fast for you.</li>
            <li><strong>Quality/Resolution:</strong> All our video files are encoded in highest possible resolution. We also have quality setting function that allows every user to enjoy streaming regardless of their internet speed.</li>
            <li><strong>Updates:</strong> Our content is updated hourly, so you will get update as fast as possible.</li>
            <li><strong>User Interface:</strong> We focus on the simple and easy to use, so you will feel the life is easier here.</li>
            <li><strong>Device Compatibility:</strong> Anikoto works fine on both desktop and mobile devices, even with old browsers.</li>
        </ul>
    <?php elseif ($page_name === 'faq'): ?>
        <h1>Frequently Asked Questions</h1>
        <div class="faq-list">
            <div class="faq-item">
                <h3>Is Anikoto free?</h3>
                <p>Yes, Anikoto is completely free to use. No registration or payment required.</p>
            </div>
            <div class="faq-item">
                <h3>Do I need to create an account?</h3>
                <p>No, you can watch anime without an account. However, creating an account allows you to track your watch history and create lists.</p>
            </div>
            <div class="faq-item">
                <h3>Is Anikoto safe?</h3>
                <p>Yes, we prioritize user safety. We monitor the platform regularly and encourage users to report any issues.</p>
            </div>
            <div class="faq-item">
                <h3>Why are some videos not loading?</h3>
                <p>Try switching to a different server. We provide multiple servers for each episode to ensure reliable streaming.</p>
            </div>
            <div class="faq-item">
                <h3>Can I request an anime?</h3>
                <p>Yes, you can use our contact form to submit anime requests. We try to add requested content as quickly as possible.</p>
            </div>
        </div>
    <?php elseif ($page_name === 'contact'): ?>
        <h1>Contact Us</h1>
        <p>Have a question, suggestion, or want to report an issue? Reach out to us and we'll get back to you as soon as possible.</p>
        <?php if (!empty($_SESSION['message'])): ?>
            <div class="alert alert-success"><?= escape($_SESSION['message']) ?></div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>
        <?php if (!empty($_SESSION['error'])): ?>
            <div class="alert alert-error"><?= escape($_SESSION['error']) ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        <form class="contact-form" action="<?= url('contact') ?>" method="POST">
            <div class="form-group">
                <label for="contactName">Name</label>
                <input type="text" id="contactName" name="name" class="form-input" required>
            </div>
            <div class="form-group">
                <label for="contactEmail">Email</label>
                <input type="email" id="contactEmail" name="email" class="form-input" required>
            </div>
            <div class="form-group">
                <label for="contactMessage">Message</label>
                <textarea id="contactMessage" name="message" class="form-input" rows="5" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Send Message</button>
        </form>
    <?php elseif ($page_name === 'dmca'): ?>
        <h1>DMCA Notice</h1>
        <p>Anikoto respects the intellectual property rights of others. We do not host any copyrighted content on our servers. All video content is embedded from third-party sources.</p>
        <p>If you believe that any content available through our service infringes upon your copyright, please contact us with the following information:</p>
        <ul>
            <li>A description of the copyrighted work you claim has been infringed</li>
            <li>A description of where the infringing material is located on our site</li>
            <li>Your contact information (address, telephone number, email)</li>
            <li>A statement that you have a good faith belief that the use is not authorized</li>
            <li>A statement, under penalty of perjury, that the information is accurate</li>
        </ul>
    <?php elseif ($page_name === 'terms'): ?>
        <h1>Terms of Service</h1>
        <p>By using Anikoto, you agree to these terms of service. If you do not agree, please do not use our service.</p>
        <p>Anikoto provides streaming links to third-party hosted content. We do not host, upload, or manage any videos on our servers. All content is embedded from third-party video hosting platforms.</p>
        <p>Users must be at least 13 years of age to use this service. We reserve the right to update these terms at any time.</p>
    <?php endif; ?>
</div>
