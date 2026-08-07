<?php
require_once __DIR__ . '/config/helpers.php';
$pageTitle = 'Privacy Policy — Hindustan Vidyut Udyog Solar';
$bannerTitle = 'Privacy Policy';
$bannerSubtitle = 'How we collect, use and protect your personal information.';

$lastUpdated = 'August 1, 2026';

// section heading => paragraphs[]
$sections = [
    'Introduction' => [
        'Hindustan Vidyut Udyog Solar respects your privacy. This policy explains what personal information we collect when you use our website or engage our services, why we collect it, how we use and protect it, and the choices available to you.',
        'By submitting an enquiry or engaging our services, you consent to the practices described in this policy.',
    ],
    'Information We Collect' => [
        'When you submit an enquiry or request a quotation, we collect the details you provide: your name, phone number, email address and the content of your message. If you proceed with a project, we additionally collect information necessary to deliver it, such as your installation address, electricity connection details, recent electricity bills, and where a subsidy application is involved, identity and bank details required by the relevant government portal.',
        'Our website may collect limited technical information automatically, such as your browser type, device type and pages visited, to help us understand how the site is used.',
    ],
    'How We Use Your Information' => [
        'We use your information to respond to enquiries, prepare quotations, conduct site surveys, deliver and support installed systems, process subsidy and distribution company applications on your behalf, issue invoices and receipts, and meet our legal and tax obligations.',
        'Where you have agreed to it, we may also send you occasional updates about our services, offers and relevant scheme changes. You can opt out of these at any time without affecting the service you receive.',
    ],
    'Sharing Your Information' => [
        'We do not sell your personal information. We share it only where necessary to deliver your project: with government portals and authorities administering subsidy schemes, with your electricity distribution company for net metering and approvals, with equipment manufacturers where a warranty claim requires it, with financing partners where you have applied for a solar loan, and with our installation and service personnel.',
        'We may also disclose information where required to do so by law, regulation, or a valid order of a court or authority.',
    ],
    'Data Security' => [
        'We apply reasonable technical and organisational measures to protect your information against unauthorised access, alteration, disclosure or destruction. Access to customer records is restricted to personnel who need it to perform their role.',
        'No method of transmission or storage is completely secure, and while we work to protect your information, we cannot guarantee absolute security.',
    ],
    'Data Retention' => [
        'We retain enquiry information for a reasonable period so that we can respond to follow-up questions. Project records, including contracts, invoices, warranty documentation and subsidy filings, are retained for as long as required to support the installed system and to comply with statutory record-keeping and tax obligations.',
    ],
    'Cookies' => [
        'Our website uses only the cookies necessary for it to function correctly, including maintaining your session and protecting forms against cross-site request forgery. You can configure your browser to refuse cookies, though parts of the site may not work as intended if you do.',
    ],
    'Your Rights' => [
        'You may request access to the personal information we hold about you, ask us to correct information that is inaccurate, request deletion of information we are not required to retain, or withdraw consent to marketing communications.',
        'To exercise any of these rights, contact us using the details below. We may need to verify your identity before acting on a request.',
    ],
    'Third-Party Links' => [
        'Our website may link to external sites, including government scheme portals. Those sites are governed by their own privacy policies, and we are not responsible for their content or practices.',
    ],
    'Changes to This Policy' => [
        'We may update this policy to reflect changes in our practices or legal obligations. The revised version takes effect when published on this page, with the date of the most recent update shown above.',
    ],
];

$anchorFor = fn(string $label): string => strtolower(preg_replace('/[^a-z0-9]+/i', '-', $label));

require __DIR__ . '/components/header.php';
require __DIR__ . '/components/page-banner.php';
require __DIR__ . '/components/legal-page.php';
require __DIR__ . '/components/footer.php';
