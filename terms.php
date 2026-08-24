<?php
require_once __DIR__ . '/config/helpers.php';
$pageTitle = 'Terms & Conditions | HVU Solar';
$metaDescription = 'The terms governing use of this website and our rooftop solar installation services.';
$bannerTitle = 'Terms & Conditions';
$bannerSubtitle = 'The terms that govern your use of our website and services.';

$lastUpdated = 'August 1, 2026';

// section heading => paragraphs[]
$sections = [
    'Acceptance of Terms' => [
        'By accessing this website, requesting a quotation, or engaging Hindustan Vidyut Udyog Solar for the supply or installation of solar equipment, you agree to be bound by these Terms & Conditions. If you do not agree with any part of these terms, please do not use our website or services.',
        'We may revise these terms from time to time. The version published on this page at the time you engage our services is the version that applies to you.',
    ],
    'Quotations and Pricing' => [
        'All quotations are based on the findings of a site survey and remain valid for the period stated on the quotation document. Prices are subject to change where site conditions differ materially from those recorded during the survey, where you request a change in system specification, or where statutory duties and taxes change before installation.',
        'A quotation is an offer to supply, not a confirmed booking. A project is confirmed only once a written work order is issued and any agreed advance payment is received.',
    ],
    'Site Survey and Feasibility' => [
        'The free site survey establishes roof orientation, shading, available area, structural suitability and electrical readiness. Generation estimates provided following the survey are engineering projections based on standard irradiance data for your location and are not guarantees of actual output.',
        'Where a survey finds a site unsuitable, or suitable only with remedial work, we will inform you in writing before any commitment is made.',
    ],
    'Installation and Customer Obligations' => [
        'You confirm that you own the property or hold the necessary permission to install equipment on it, and that you will provide safe access to the installation area, along with electricity and water as reasonably required during the works.',
        'You are responsible for obtaining any society, association, municipal or landlord approvals that apply to your property. Delays arising from access restrictions, unavailable approvals, or site conditions not disclosed at survey may affect the schedule and, where additional work is required, the price.',
    ],
    'Subsidy and Regulatory Approvals' => [
        'Where your project involves a government subsidy such as PM Surya Ghar Muft Bijli Yojana, we will assist with registration, documentation and liaison with your distribution company. Subsidy amounts, eligibility criteria and disbursement timelines are determined solely by the relevant government authority.',
        'We cannot guarantee approval, the amount sanctioned, or the timing of disbursement, as these lie outside our control. Subsidy is credited by the authority to your bank account after installation and inspection.',
    ],
    'Payment Terms' => [
        'Payment milestones are set out in your quotation and work order, and typically comprise an advance on confirmation, a payment on material delivery, and a balance on commissioning. Amounts due to the government or your distribution company, such as net meter charges or application fees, are payable by you unless expressly stated otherwise.',
        'Title in supplied equipment passes to you only upon receipt of full payment. Overdue amounts may attract interest at the rate stated in your work order.',
    ],
    'Warranties' => [
        'Solar panels, inverters and other manufactured components carry warranties issued by their respective manufacturers, and the applicable terms and durations are listed in your quotation. We pass these through to you in full and assist with claims, but we are not the warrantor of manufactured goods.',
        'Our own workmanship in relation to installation is warranted separately for the period stated in your work order. Warranty cover excludes damage caused by misuse, unauthorised modification, tampering, animal or pest damage, or events beyond reasonable control such as storm, flood, lightning or fire.',
    ],
    'Limitation of Liability' => [
        'To the fullest extent permitted by law, our total liability arising out of or in connection with any project is limited to the value of the contract for that project. We are not liable for indirect or consequential loss, including loss of savings, loss of expected generation, business interruption, or loss arising from grid outages or distribution company action.',
        'Nothing in these terms excludes or limits liability for death or personal injury caused by negligence, or for any other liability that cannot lawfully be excluded.',
    ],
    'Cancellation' => [
        'You may cancel a confirmed order in writing before materials are dispatched, subject to deduction of costs already reasonably incurred, including survey, design and documentation work. Once materials are dispatched or installation has commenced, cancellation charges will reflect the actual costs and committed supply obligations at that point.',
    ],
    'Intellectual Property' => [
        'All content on this website, including text, designs, images, layouts and logos, is the property of Hindustan Vidyut Udyog Solar or its licensors and may not be reproduced, distributed or used commercially without prior written permission.',
    ],
    'Governing Law' => [
        'These terms are governed by the laws of India. Any dispute arising out of or in connection with these terms or any project shall be subject to the exclusive jurisdiction of the competent courts at our registered place of business.',
    ],
];

$anchorFor = fn(string $label): string => strtolower(preg_replace('/[^a-z0-9]+/i', '-', $label));

require __DIR__ . '/components/header.php';
require __DIR__ . '/components/page-banner.php';
require __DIR__ . '/components/legal-page.php';
require __DIR__ . '/components/footer.php';
