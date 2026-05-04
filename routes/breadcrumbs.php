<?php

use Diglactic\Breadcrumbs\Breadcrumbs;
use Diglactic\Breadcrumbs\Generator as BreadcrumbTrail;

// Home
Breadcrumbs::for('home', function (BreadcrumbTrail $trail) {
    $trail->push(translate('Home'), route('home'));
});

// Premium Membership
Breadcrumbs::for('premium', function (BreadcrumbTrail $trail) {
    $trail->parent('home');
    $trail->push(translate('Premium Membership'), route('premium.plans'));
});

// Contact
Breadcrumbs::for('contact', function (BreadcrumbTrail $trail) {
    $trail->parent('home');
    $trail->push(translate('Contact US'), route('contact'));
});

// Favorites
Breadcrumbs::for('favorites', function (BreadcrumbTrail $trail) {
    $trail->parent('home');
    $trail->push(translate('Favorites'), route('favorites'));
});

// Categories
Breadcrumbs::for('categories', function (BreadcrumbTrail $trail) {
    $trail->parent('home');
    $trail->push(translate('Categories'), route('categories.index'));
});

Breadcrumbs::for('categories.category', function (BreadcrumbTrail $trail, $category) {
    $trail->parent('categories');
    $trail->push($category->name, $category->link);
});

Breadcrumbs::for('categories.sub-category', function (BreadcrumbTrail $trail, $category, $subCategory) {
    $trail->parent('categories.category', $category);
    $trail->push($subCategory->name, $subCategory->view_link);
});

// Products
Breadcrumbs::for('products', function (BreadcrumbTrail $trail) {
    $trail->parent('home');
    $trail->push(translate('Products'), route('products.index'));
});

Breadcrumbs::for('products.show', function (BreadcrumbTrail $trail, $product, $data = null) {
    $trail->parent('products');
    $trail->push($product->category->name, $product->category->view_link);
    if ($product->subCategory) {
        $trail->push($product->subCategory->name, $product->subCategory->view_link);
    }

    // Check if we should push title
    $showTitle = ($data && property_exists($data, 'breadcrumb_show_title'))
        ? (bool) $data->breadcrumb_show_title
        : true;

    if ($showTitle) {
        $trail->push($product->name, $product->view_link);
    }
});

Breadcrumbs::for('products.reviews', function (BreadcrumbTrail $trail, $product) {
    $trail->parent('products.show', $product);
    $trail->push(translate('Reviews'), $product->getReviewsLink());
});

Breadcrumbs::for('products.reviews.review', function (BreadcrumbTrail $trail, $product, $review) {
    $trail->parent('products.reviews', $product);
    $trail->push($review->id, $review->view_link);
});

Breadcrumbs::for('products.comments', function (BreadcrumbTrail $trail, $product) {
    $trail->parent('products.show', $product);
    $trail->push(translate('Comments'), $product->getCommentsLink());
});

Breadcrumbs::for('products.comments.comment', function (BreadcrumbTrail $trail, $product, $comment) {
    $trail->parent('products.comments', $product);
    $trail->push($comment->id, $comment->view_link);
});

Breadcrumbs::for('products.changelogs', function (BreadcrumbTrail $trail, $product) {
    $trail->parent('products.show', $product);
    $trail->push(translate('Changelogs'), $product->getChangeLogsLink());
});

Breadcrumbs::for('products.support', function (BreadcrumbTrail $trail, $product) {
    $trail->parent('products.show', $product);
    $trail->push(translate('Support'), $product->getSupportLink());
});

// Cart & Checkout
Breadcrumbs::for('cart', function (BreadcrumbTrail $trail) {
    $trail->parent('home');
    $trail->push(translate('Cart'), route('cart.index'));
});

Breadcrumbs::for('checkout', function (BreadcrumbTrail $trail, $transaction) {
    $trail->parent('cart');
    $trail->push(translate('Checkout'), route('checkout.index', $transaction->id));
});

// Pages
Breadcrumbs::for('page', function (BreadcrumbTrail $trail, $page) {
    $trail->parent('home');
    $trail->push($page->title, $page->link);
});

// Help Center
Breadcrumbs::for('help', function (BreadcrumbTrail $trail) {
    $trail->parent('home');
    $trail->push(translate('Help Center'), route('help.index'));
});

Breadcrumbs::for('help_category', function (BreadcrumbTrail $trail, $helpCategory) {
    $trail->parent('help');
    $trail->push($helpCategory->name, $helpCategory->view_link);
});

Breadcrumbs::for('help_article', function (BreadcrumbTrail $trail, $helpArticle) {
    $trail->parent('help_category', $helpArticle->category);
    $trail->push($helpArticle->title, $helpArticle->view_link);
});

// Blog
Breadcrumbs::for('blog', function (BreadcrumbTrail $trail) {
    $trail->parent('home');
    $trail->push(translate('Blog'), route('blog.index'));
});

Breadcrumbs::for('blog_category', function (BreadcrumbTrail $trail, $blogCategory) {
    $trail->parent('blog');
    $trail->push($blogCategory->name, route('blog.category', $blogCategory->slug));
});

Breadcrumbs::for('blog_article', function (BreadcrumbTrail $trail, $blogArticle) {
    $trail->parent('blog_category', $blogArticle->category);
    $trail->push($blogArticle->title, $blogArticle->view_link);
});

// Portal
Breadcrumbs::for('portal', function (BreadcrumbTrail $trail) {
    $trail->push(translate('Portal'), route('user.index'));
});

Breadcrumbs::for('user.dashboard', function (BreadcrumbTrail $trail) {
    $trail->parent('portal');
    $trail->push(translate('Dashboard'), route('user.dashboard'));
});

// Portal Products
Breadcrumbs::for('user.product', function (BreadcrumbTrail $trail) {
    $trail->parent('portal');
    $trail->push(translate('My Products'), route('user.product.index'));
});

Breadcrumbs::for('user.product.create', function (BreadcrumbTrail $trail) {
    $trail->parent('user.product');
    $trail->push(translate('New Product'), route('user.product.create'));
});

Breadcrumbs::for('user.product.drafts', function (BreadcrumbTrail $trail) {
    $trail->parent('user.product');
    $trail->push(translate('Drafts'), route('user.product.drafts'));
});

Breadcrumbs::for('user.product.edit', function (BreadcrumbTrail $trail, $product) {
    $trail->parent('user.product');
    $trail->push($product->id, route('user.product.edit', $product->id));
});

Breadcrumbs::for('user.product.changelogs.index', function (BreadcrumbTrail $trail, $product) {
    $trail->parent('user.product.edit', $product);
    $trail->push(translate('Changelogs'), route('user.product.changelogs.index', $product->id));
});

Breadcrumbs::for('user.product.history', function (BreadcrumbTrail $trail, $product) {
    $trail->parent('user.product.edit', $product);
    $trail->push(translate('History'), route('user.product.history', $product->id));
});

Breadcrumbs::for('user.product.discount', function (BreadcrumbTrail $trail, $product) {
    $trail->parent('user.product.edit', $product);
    $trail->push(translate('Discount'), route('user.product.discount', $product->id));
});

Breadcrumbs::for('user.product.free', function (BreadcrumbTrail $trail, $product) {
    $trail->parent('user.product.edit', $product);
    $trail->push(translate('Free Product'), route('user.product.free', $product->id));
});

Breadcrumbs::for('user.product.statistics', function (BreadcrumbTrail $trail, $product) {
    $trail->parent('user.product.edit', $product);
    $trail->push(translate('Statistics'), route('user.product.statistics', $product->id));
});

// Portal Purchases
Breadcrumbs::for('user.purchase', function (BreadcrumbTrail $trail) {
    $trail->parent('portal');
    $trail->push(translate('Purchases'), route('user.purchase.index'));
});

// Portal Transactions
Breadcrumbs::for('user.transaction', function (BreadcrumbTrail $trail) {
    $trail->parent('portal');
    $trail->push(translate('Transactions'), route('user.transaction.index'));
});

Breadcrumbs::for('user.transaction.show', function (BreadcrumbTrail $trail, $trx) {
    $trail->parent('user.transaction');
    $trail->push($trx->id, route('user.transaction.show', $trx->id));
});

// Portal Referrals
Breadcrumbs::for('user.referrals', function (BreadcrumbTrail $trail) {
    $trail->parent('portal');
    $trail->push(translate('Referrals'), route('user.referrals'));
});

// Portal Payouts
Breadcrumbs::for('user.payout', function (BreadcrumbTrail $trail) {
    $trail->parent('portal');
    $trail->push(translate('Payouts'), route('user.payout.index'));
});

// Portal Wallet
Breadcrumbs::for('user.wallet.index', function (BreadcrumbTrail $trail) {
    $trail->parent('portal');
    $trail->push(translate('My Wallet'), route('user.wallet.index'));
});

// Portal Refunds
Breadcrumbs::for('user.refund.index', function (BreadcrumbTrail $trail) {
    $trail->parent('portal');
    $trail->push(translate('Refunds'), route('user.refund.index'));
});

Breadcrumbs::for('user.refund.create', function (BreadcrumbTrail $trail) {
    $trail->parent('user.refund.index');
    $trail->push(translate('Request a Refund'), route('user.refund.create'));
});

Breadcrumbs::for('user.refund.show', function (BreadcrumbTrail $trail, $refund) {
    $trail->parent('user.refund.index');
    $trail->push($refund->id, route('user.refund.show', $refund->id));
});

// Portal Tickets
Breadcrumbs::for('user.ticket.index', function (BreadcrumbTrail $trail) {
    $trail->parent('portal');
    $trail->push(translate('Tickets'), route('user.ticket.index'));
});

Breadcrumbs::for('user.ticket.create', function (BreadcrumbTrail $trail) {
    $trail->parent('user.ticket.index');
    $trail->push(translate('New Ticket'), route('user.ticket.create'));
});

Breadcrumbs::for('user.ticket.show', function (BreadcrumbTrail $trail, $ticket) {
    $trail->parent('user.ticket.index');
    $trail->push($ticket->id, route('user.ticket.show', $ticket->id));
});

// Portal Tools
Breadcrumbs::for('user.tool', function (BreadcrumbTrail $trail) {
    $trail->parent('portal');
    $trail->push(translate('Tools'), route('user.tool.index'));
});

Breadcrumbs::for('user.tool.license-verification', function (BreadcrumbTrail $trail) {
    $trail->parent('user.tool');
    $trail->push(translate('License Verification'), route('user.tool.license-verification.index'));
});

// Portal Settings
Breadcrumbs::for('user.setting', function (BreadcrumbTrail $trail) {
    $trail->parent('portal');
    $trail->push(translate('Settings'), route('user.settings.index'));
});

Breadcrumbs::for('user.settings.profile', function (BreadcrumbTrail $trail) {
    $trail->parent('user.setting');
    $trail->push(translate('Profile Details'), route('user.settings.profile'));
});

Breadcrumbs::for('user.settings.payout', function (BreadcrumbTrail $trail) {
    $trail->parent('user.setting');
    $trail->push(translate('Payout Details'), route('user.settings.payout'));
});

Breadcrumbs::for('user.settings.subscription', function (BreadcrumbTrail $trail) {
    $trail->parent('user.settings');
    $trail->push(translate('Premium Membership'), route('user.settings.subscription'));
});

Breadcrumbs::for('user.settings.badges', function (BreadcrumbTrail $trail) {
    $trail->parent('user.setting');
    $trail->push(translate('My Badges'), route('user.settings.badges'));
});

Breadcrumbs::for('user.settings.api-key', function (BreadcrumbTrail $trail) {
    $trail->parent('user.setting');
    $trail->push(translate('API Key'), route('user.settings.api-key'));
});

Breadcrumbs::for('user.settings.password', function (BreadcrumbTrail $trail) {
    $trail->parent('user.setting');
    $trail->push(translate('Change Password'), route('user.settings.password'));
});

Breadcrumbs::for('user.settings.2fa', function (BreadcrumbTrail $trail) {
    $trail->parent('user.setting');
    $trail->push(translate('2FA Authentication'), route('user.settings.2fa'));
});

Breadcrumbs::for('user.settings.id-verification', function (BreadcrumbTrail $trail) {
    $trail->parent('user.setting');
    $trail->push(translate('ID Verification'), route('user.settings.id-verification'));
});
