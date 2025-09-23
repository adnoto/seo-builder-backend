<?php

declare(strict_types=1);

return [
    'archetypes' => [
        'services' => [
            'name' => 'Services Company',
            'description' => 'SEO-optimized flow for service-based businesses (e.g., contractors, consultants).',
            'pages' => [
                [
                    'page_type' => 'home',
                    'slug' => '/',
                    'title' => 'Home',
                    'meta_description' => 'Welcome to [Business Name] - Your trusted [Service] in [Location].',
                    'seo_data' => [
                        'canonical' => '/',
                        'robots' => 'index,follow',
                        'schema' => ['LocalBusiness' => true, 'Service' => true, 'FAQ' => false],
                        'keywords' => ['[service] [location]', '[service] near me'],
                    ],
                    'page_structure' => [
                        'version' => '1.0',
                        'components' => [
                            [
                                'id' => 'hero-1',
                                'type' => 'Hero',
                                'props' => [
                                    'headline' => '[Business Name] - Expert [Service] in [Location]',
                                    'sub' => 'Discover top-quality [service] with a focus on results.',
                                    'cta' => 'Get a Quote',
                                    'aria_label' => 'Main hero section',
                                ],
                                'prompt_metadata' => ['maxLength' => 50, 'readingLevel' => 8],
                            ],
                            [
                                'id' => 'services-grid-1',
                                'type' => 'ServicesGrid',
                                'props' => [
                                    'items' => [
                                        ['title' => '[Service 1]', 'desc' => '', 'link' => '/services/[service-1]'],
                                        ['title' => '[Service 2]', 'desc' => '', 'link' => '/services/[service-2]'],
                                    ],
                                    'aria_label' => 'Services overview',
                                ],
                                'prompt_metadata' => ['maxLength' => 100, 'readingLevel' => 8],
                            ],
                            [
                                'id' => 'testimonials-1',
                                'type' => 'Testimonials',
                                'props' => [
                                    'source' => 'manual',
                                    'items' => [],
                                    'aria_label' => 'Customer testimonials',
                                ],
                                'prompt_metadata' => ['maxLength' => 150, 'readingLevel' => 8],
                            ],
                            [
                                'id' => 'cta-1',
                                'type' => 'CTA',
                                'props' => [
                                    'text' => 'Schedule a Consultation',
                                    'link' => '/contact',
                                    'aria_label' => 'Call to action',
                                ],
                                'prompt_metadata' => ['maxLength' => 30, 'readingLevel' => 8],
                            ],
                        ],
                    ],
                ],
                [
                    'page_type' => 'services',
                    'slug' => 'services',
                    'title' => 'Our Services',
                    'meta_description' => 'Explore our [Service] offerings in [Location].',
                    'seo_data' => [
                        'canonical' => '/services',
                        'robots' => 'index,follow',
                        'schema' => ['Service' => true],
                        'keywords' => ['[service] services [location]', '[service] provider'],
                    ],
                    'page_structure' => [
                        'version' => '1.0',
                        'components' => [
                            [
                                'id' => 'services-list-1',
                                'type' => 'ServicesList',
                                'props' => [
                                    'items' => [
                                        ['title' => '[Service 1]', 'desc' => '', 'link' => '/services/[service-1]'],
                                        ['title' => '[Service 2]', 'desc' => '', 'link' => '/services/[service-2]'],
                                    ],
                                    'aria_label' => 'Detailed services list',
                                ],
                                'prompt_metadata' => ['maxLength' => 150, 'readingLevel' => 8],
                            ],
                            [
                                'id' => 'faq-1',
                                'type' => 'FAQ',
                                'props' => [
                                    'items' => [],
                                    'aria_label' => 'Frequently asked questions',
                                ],
                                'prompt_metadata' => ['maxLength' => 200, 'readingLevel' => 8],
                            ],
                            [
                                'id' => 'cta-2',
                                'type' => 'CTA',
                                'props' => [
                                    'text' => 'Contact Us Today',
                                    'link' => '/contact',
                                    'aria_label' => 'Call to action',
                                ],
                                'prompt_metadata' => ['maxLength' => 30, 'readingLevel' => 8],
                            ],
                        ],
                    ],
                ],
                [
                    'page_type' => 'about',
                    'slug' => 'about',
                    'title' => 'About Us',
                    'meta_description' => 'Learn about [Business Name] - Our story and values.',
                    'seo_data' => [
                        'canonical' => '/about',
                        'robots' => 'index,follow',
                        'schema' => ['Organization' => true],
                        'keywords' => ['[business] about', '[business] story [location]'],
                    ],
                    'page_structure' => [
                        'version' => '1.0',
                        'components' => [
                            [
                                'id' => 'story-1',
                                'type' => 'Story',
                                'props' => [
                                    'title' => 'Our Story',
                                    'content' => '',
                                    'aria_label' => 'Company story',
                                ],
                                'prompt_metadata' => ['maxLength' => 300, 'readingLevel' => 8],
                            ],
                            [
                                'id' => 'team-1',
                                'type' => 'Team',
                                'props' => [
                                    'items' => [],
                                    'aria_label' => 'Team members',
                                ],
                                'prompt_metadata' => ['maxLength' => 150, 'readingLevel' => 8],
                            ],
                            [
                                'id' => 'values-1',
                                'type' => 'Values',
                                'props' => [
                                    'items' => [],
                                    'aria_label' => 'Company values',
                                ],
                                'prompt_metadata' => ['maxLength' => 100, 'readingLevel' => 8],
                            ],
                            [
                                'id' => 'cta-3',
                                'type' => 'CTA',
                                'props' => [
                                    'text' => 'Get in Touch',
                                    'link' => '/contact',
                                    'aria_label' => 'Call to action',
                                ],
                                'prompt_metadata' => ['maxLength' => 30, 'readingLevel' => 8],
                            ],
                        ],
                    ],
                ],
                [
                    'page_type' => 'contact',
                    'slug' => 'contact',
                    'title' => 'Contact Us',
                    'meta_description' => 'Contact [Business Name] for [Service] in [Location].',
                    'seo_data' => [
                        'canonical' => '/contact',
                        'robots' => 'index,follow',
                        'schema' => ['LocalBusiness' => true],
                        'keywords' => ['[business] contact [location]', '[service] inquiry'],
                    ],
                    'page_structure' => [
                        'version' => '1.0',
                        'components' => [
                            [
                                'id' => 'form-1',
                                'type' => 'ContactForm',
                                'props' => [
                                    'fields' => ['name', 'email', 'message'],
                                    'aria_label' => 'Contact form',
                                ],
                                'prompt_metadata' => ['maxLength' => 50, 'readingLevel' => 8],
                            ],
                            [
                                'id' => 'map-1',
                                'type' => 'Map',
                                'props' => [
                                    'location' => '[Business Address]',
                                    'aria_label' => 'Business location map',
                                ],
                                'prompt_metadata' => [], // No AI content needed
                            ],
                            [
                                'id' => 'info-1',
                                'type' => 'BusinessInfo',
                                'props' => [
                                    'phone' => '[Business Phone]',
                                    'email' => '[Business Email]',
                                    'aria_label' => 'Business contact information',
                                ],
                                'prompt_metadata' => ['maxLength' => 50, 'readingLevel' => 8],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'products' => [
            'name' => 'Products Company',
            'description' => 'E-commerce flow with product focus and social proof.',
            'pages' => [
                [
                    'page_type' => 'home',
                    'slug' => '/',
                    'title' => 'Home',
                    'meta_description' => 'Shop [Business Name] for top [Product Category] in [Location].',
                    'seo_data' => [
                        'canonical' => '/',
                        'robots' => 'index,follow',
                        'schema' => ['LocalBusiness' => true, 'Product' => true],
                        'keywords' => ['[product] [location]', 'buy [product] online'],
                    ],
                    'page_structure' => [
                        'version' => '1.0',
                        'components' => [
                            [
                                'id' => 'hero-1',
                                'type' => 'Hero',
                                'props' => [
                                    'headline' => 'Discover [Product Category] at [Business Name]',
                                    'sub' => 'Quality products with fast delivery.',
                                    'cta' => 'Shop Now',
                                    'aria_label' => 'Main hero section',
                                ],
                                'prompt_metadata' => ['maxLength' => 50, 'readingLevel' => 8],
                            ],
                            [
                                'id' => 'product-grid-1',
                                'type' => 'ProductGrid',
                                'props' => [
                                    'items' => [
                                        ['title' => '[Product 1]', 'desc' => '', 'link' => '/products/[product-1]'],
                                        ['title' => '[Product 2]', 'desc' => '', 'link' => '/products/[product-2]'],
                                    ],
                                    'aria_label' => 'Featured products',
                                ],
                                'prompt_metadata' => ['maxLength' => 100, 'readingLevel' => 8],
                            ],
                            [
                                'id' => 'social-proof-1',
                                'type' => 'SocialProof',
                                'props' => [
                                    'items' => [],
                                    'aria_label' => 'Customer reviews and logos',
                                ],
                                'prompt_metadata' => ['maxLength' => 150, 'readingLevel' => 8],
                            ],
                            [
                                'id' => 'cta-1',
                                'type' => 'CTA',
                                'props' => [
                                    'text' => 'Browse All Products',
                                    'link' => '/products',
                                    'aria_label' => 'Call to action',
                                ],
                                'prompt_metadata' => ['maxLength' => 30, 'readingLevel' => 8],
                            ],
                        ],
                    ],
                ],
                [
                    'page_type' => 'products',
                    'slug' => 'products',
                    'title' => 'Our Products',
                    'meta_description' => 'Browse our [Product Category] collection at [Business Name].',
                    'seo_data' => [
                        'canonical' => '/products',
                        'robots' => 'index,follow',
                        'schema' => ['Product' => true],
                        'keywords' => ['[product] catalog', '[product] for sale [location]'],
                    ],
                    'page_structure' => [
                        'version' => '1.0',
                        'components' => [
                            [
                                'id' => 'product-grid-2',
                                'type' => 'ProductGrid',
                                'props' => [
                                    'items' => [
                                        ['title' => '[Product 1]', 'desc' => '', 'link' => '/products/[product-1]'],
                                        ['title' => '[Product 2]', 'desc' => '', 'link' => '/products/[product-2]'],
                                    ],
                                    'aria_label' => 'Product catalog',
                                ],
                                'prompt_metadata' => ['maxLength' => 150, 'readingLevel' => 8],
                            ],
                            [
                                'id' => 'product-detail-template-1',
                                'type' => 'ProductDetailTemplate',
                                'props' => [
                                    'template' => 'default',
                                    'aria_label' => 'Product detail template',
                                ],
                                'prompt_metadata' => ['maxLength' => 200, 'readingLevel' => 8],
                            ],
                            [
                                'id' => 'cta-2',
                                'type' => 'CTA',
                                'props' => [
                                    'text' => 'Shop Now',
                                    'link' => '/products',
                                    'aria_label' => 'Call to action',
                                ],
                                'prompt_metadata' => ['maxLength' => 30, 'readingLevel' => 8],
                            ],
                        ],
                    ],
                ],
                [
                    'page_type' => 'about',
                    'slug' => 'about',
                    'title' => 'About Us',
                    'meta_description' => 'Learn about [Business Name] - Our brand and mission.',
                    'seo_data' => [
                        'canonical' => '/about',
                        'robots' => 'index,follow',
                        'schema' => ['Organization' => true],
                        'keywords' => ['[business] about', '[business] mission [location]'],
                    ],
                    'page_structure' => [
                        'version' => '1.0',
                        'components' => [
                            [
                                'id' => 'story-1',
                                'type' => 'BrandStory',
                                'props' => [
                                    'title' => 'Our Story',
                                    'content' => '',
                                    'aria_label' => 'Brand story',
                                ],
                                'prompt_metadata' => ['maxLength' => 300, 'readingLevel' => 8],
                            ],
                            [
                                'id' => 'team-1',
                                'type' => 'Team',
                                'props' => [
                                    'items' => [],
                                    'aria_label' => 'Team members',
                                ],
                                'prompt_metadata' => ['maxLength' => 150, 'readingLevel' => 8],
                            ],
                            [
                                'id' => 'process-1',
                                'type' => 'Process',
                                'props' => [
                                    'title' => 'Our Process',
                                    'content' => '',
                                    'aria_label' => 'Manufacturing process',
                                ],
                                'prompt_metadata' => ['maxLength' => 200, 'readingLevel' => 8],
                            ],
                            [
                                'id' => 'cta-3',
                                'type' => 'CTA',
                                'props' => [
                                    'text' => 'Contact Us',
                                    'link' => '/contact',
                                    'aria_label' => 'Call to action',
                                ],
                                'prompt_metadata' => ['maxLength' => 30, 'readingLevel' => 8],
                            ],
                        ],
                    ],
                ],
                [
                    'page_type' => 'contact',
                    'slug' => 'contact',
                    'title' => 'Contact Us',
                    'meta_description' => 'Contact [Business Name] for [Product Category] support.',
                    'seo_data' => [
                        'canonical' => '/contact',
                        'robots' => 'index,follow',
                        'schema' => ['LocalBusiness' => true],
                        'keywords' => ['[business] contact [location]', '[product] support'],
                    ],
                    'page_structure' => [
                        'version' => '1.0',
                        'components' => [
                            [
                                'id' => 'form-1',
                                'type' => 'ContactForm',
                                'props' => [
                                    'fields' => ['name', 'email', 'message'],
                                    'aria_label' => 'Contact form',
                                ],
                                'prompt_metadata' => ['maxLength' => 50, 'readingLevel' => 8],
                            ],
                            [
                                'id' => 'locations-1',
                                'type' => 'Locations',
                                'props' => [
                                    'items' => [],
                                    'aria_label' => 'Store locations',
                                ],
                                'prompt_metadata' => ['maxLength' => 100, 'readingLevel' => 8],
                            ],
                            [
                                'id' => 'support-1',
                                'type' => 'SupportInfo',
                                'props' => [
                                    'phone' => '[Business Phone]',
                                    'email' => '[Business Email]',
                                    'aria_label' => 'Support information',
                                ],
                                'prompt_metadata' => ['maxLength' => 50, 'readingLevel' => 8],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'professional' => [
            'name' => 'Professional Services',
            'description' => 'For lawyers, accountants, consultants with credential focus.',
            'pages' => [
                [
                    'page_type' => 'home',
                    'slug' => '/',
                    'title' => 'Home',
                    'meta_description' => 'Welcome to [Business Name] - Expert [Service] in [Location].',
                    'seo_data' => [
                        'canonical' => '/',
                        'robots' => 'index,follow',
                        'schema' => ['ProfessionalService' => true, 'LocalBusiness' => true],
                        'keywords' => ['[service] [location]', '[service] consultant'],
                    ],
                    'page_structure' => [
                        'version' => '1.0',
                        'components' => [
                            [
                                'id' => 'hero-1',
                                'type' => 'Hero',
                                'props' => [
                                    'headline' => '[Business Name] - Trusted [Service] Experts',
                                    'sub' => 'Delivering professional [service] solutions in [Location].',
                                    'cta' => 'Book a Consultation',
                                    'aria_label' => 'Main hero section',
                                ],
                                'prompt_metadata' => ['maxLength' => 50, 'readingLevel' => 8],
                            ],
                            [
                                'id' => 'services-summary-1',
                                'type' => 'ServicesSummary',
                                'props' => [
                                    'items' => [
                                        ['title' => '[Service 1]', 'desc' => ''],
                                        ['title' => '[Service 2]', 'desc' => ''],
                                    ],
                                    'aria_label' => 'Services summary',
                                ],
                                'prompt_metadata' => ['maxLength' => 100, 'readingLevel' => 8],
                            ],
                            [
                                'id' => 'case-studies-1',
                                'type' => 'CaseStudies',
                                'props' => [
                                    'items' => [],
                                    'aria_label' => 'Client case studies',
                                ],
                                'prompt_metadata' => ['maxLength' => 150, 'readingLevel' => 8],
                            ],
                            [
                                'id' => 'testimonials-1',
                                'type' => 'Testimonials',
                                'props' => [
                                    'source' => 'manual',
                                    'items' => [],
                                    'aria_label' => 'Client testimonials',
                                ],
                                'prompt_metadata' => ['maxLength' => 150, 'readingLevel' => 8],
                            ],
                            [
                                'id' => 'cta-1',
                                'type' => 'CTA',
                                'props' => [
                                    'text' => 'Schedule a Consultation',
                                    'link' => '/contact',
                                    'aria_label' => 'Call to action',
                                ],
                                'prompt_metadata' => ['maxLength' => 30, 'readingLevel' => 8],
                            ],
                        ],
                    ],
                ],
                [
                    'page_type' => 'services',
                    'slug' => 'services',
                    'title' => 'Our Services',
                    'meta_description' => 'Discover our professional [Service] offerings in [Location].',
                    'seo_data' => [
                        'canonical' => '/services',
                        'robots' => 'index,follow',
                        'schema' => ['ProfessionalService' => true],
                        'keywords' => ['[service] services [location]', '[service] expert'],
                    ],
                    'page_structure' => [
                        'version' => '1.0',
                        'components' => [
                            [
                                'id' => 'detailed-services-1',
                                'type' => 'DetailedServices',
                                'props' => [
                                    'items' => [
                                        ['title' => '[Service 1]', 'desc' => '', 'link' => '/services/[service-1]'],
                                        ['title' => '[Service 2]', 'desc' => '', 'link' => '/services/[service-2]'],
                                    ],
                                    'aria_label' => 'Detailed services',
                                ],
                                'prompt_metadata' => ['maxLength' => 200, 'readingLevel' => 8],
                            ],
                            [
                                'id' => 'faq-1',
                                'type' => 'FAQ',
                                'props' => [
                                    'items' => [],
                                    'aria_label' => 'Frequently asked questions',
                                ],
                                'prompt_metadata' => ['maxLength' => 200, 'readingLevel' => 8],
                            ],
                            [
                                'id' => 'cta-2',
                                'type' => 'CTA',
                                'props' => [
                                    'text' => 'Contact Us Today',
                                    'link' => '/contact',
                                    'aria_label' => 'Call to action',
                                ],
                                'prompt_metadata' => ['maxLength' => 30, 'readingLevel' => 8],
                            ],
                        ],
                    ],
                ],
                [
                    'page_type' => 'team',
                    'slug' => 'team',
                    'title' => 'Our Team',
                    'meta_description' => 'Meet the [Business Name] team of [Service] professionals.',
                    'seo_data' => [
                        'canonical' => '/team',
                        'robots' => 'index,follow',
                        'schema' => ['Person' => true],
                        'keywords' => ['[business] team [location]', '[service] professionals'],
                    ],
                    'page_structure' => [
                        'version' => '1.0',
                        'components' => [
                            [
                                'id' => 'team-bios-1',
                                'type' => 'TeamBios',
                                'props' => [
                                    'items' => [],
                                    'aria_label' => 'Team member bios',
                                ],
                                'prompt_metadata' => ['maxLength' => 150, 'readingLevel' => 8],
                            ],
                            [
                                'id' => 'credentials-1',
                                'type' => 'Credentials',
                                'props' => [
                                    'items' => [],
                                    'aria_label' => 'Professional credentials',
                                ],
                                'prompt_metadata' => ['maxLength' => 100, 'readingLevel' => 8],
                            ],
                            [
                                'id' => 'associations-1',
                                'type' => 'Associations',
                                'props' => [
                                    'items' => [],
                                    'aria_label' => 'Professional associations',
                                ],
                                'prompt_metadata' => ['maxLength' => 100, 'readingLevel' => 8],
                            ],
                        ],
                    ],
                ],
                [
                    'page_type' => 'contact',
                    'slug' => 'contact',
                    'title' => 'Contact Us',
                    'meta_description' => 'Book a consultation with [Business Name] for [Service].',
                    'seo_data' => [
                        'canonical' => '/contact',
                        'robots' => 'index,follow',
                        'schema' => ['LocalBusiness' => true],
                        'keywords' => ['[business] contact [location]', '[service] consultation'],
                    ],
                    'page_structure' => [
                        'version' => '1.0',
                        'components' => [
                            [
                                'id' => 'consultation-form-1',
                                'type' => 'ConsultationForm',
                                'props' => [
                                    'fields' => ['name', 'email', 'message', 'preferred_time'],
                                    'aria_label' => 'Consultation form',
                                ],
                                'prompt_metadata' => ['maxLength' => 50, 'readingLevel' => 8],
                            ],
                            [
                                'id' => 'map-1',
                                'type' => 'Map',
                                'props' => [
                                    'location' => '[Business Address]',
                                    'aria_label' => 'Office location map',
                                ],
                                'prompt_metadata' => [], // No AI content needed
                            ],
                            [
                                'id' => 'office-hours-1',
                                'type' => 'OfficeHours',
                                'props' => [
                                    'hours' => '',
                                    'aria_label' => 'Office hours',
                                ],
                                'prompt_metadata' => ['maxLength' => 50, 'readingLevel' => 8],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'portfolio' => [
            'name' => 'Portfolio / Creative',
            'description' => 'Visual-first flow for creatives with project showcases.',
            'pages' => [
                [
                    'page_type' => 'home',
                    'slug' => '/',
                    'title' => 'Home',
                    'meta_description' => 'Explore [Business Name]’s creative portfolio in [Location].',
                    'seo_data' => [
                        'canonical' => '/',
                        'robots' => 'index,follow',
                        'schema' => ['CreativeWork' => true, 'LocalBusiness' => true],
                        'keywords' => ['[business] portfolio [location]', '[business] creative'],
                    ],
                    'page_structure' => [
                        'version' => '1.0',
                        'components' => [
                            [
                                'id' => 'hero-1',
                                'type' => 'Hero',
                                'props' => [
                                    'headline' => '[Business Name] - Creative Excellence',
                                    'sub' => 'Showcasing our best work in [Location].',
                                    'cta' => 'View Portfolio',
                                    'aria_label' => 'Main hero section',
                                ],
                                'prompt_metadata' => ['maxLength' => 50, 'readingLevel' => 8],
                            ],
                            [
                                'id' => 'featured-work-1',
                                'type' => 'FeaturedWork',
                                'props' => [
                                    'items' => [],
                                    'aria_label' => 'Featured portfolio projects',
                                ],
                                'prompt_metadata' => ['maxLength' => 100, 'readingLevel' => 8],
                            ],
                            [
                                'id' => 'testimonials-1',
                                'type' => 'Testimonials',
                                'props' => [
                                    'source' => 'manual',
                                    'items' => [],
                                    'aria_label' => 'Client testimonials',
                                ],
                                'prompt_metadata' => ['maxLength' => 150, 'readingLevel' => 8],
                            ],
                            [
                                'id' => 'cta-1',
                                'type' => 'CTA',
                                'props' => [
                                    'text' => 'See All Projects',
                                    'link' => '/portfolio',
                                    'aria_label' => 'Call to action',
                                ],
                                'prompt_metadata' => ['maxLength' => 30, 'readingLevel' => 8],
                            ],
                        ],
                    ],
                ],
                [
                    'page_type' => 'portfolio',
                    'slug' => 'portfolio',
                    'title' => 'Our Portfolio',
                    'meta_description' => 'Discover [Business Name]’s creative projects and case studies.',
                    'seo_data' => [
                        'canonical' => '/portfolio',
                        'robots' => 'index,follow',
                        'schema' => ['CreativeWork' => true],
                        'keywords' => ['[business] portfolio', '[business] projects [location]'],
                    ],
                    'page_structure' => [
                        'version' => '1.0',
                        'components' => [
                            [
                                'id' => 'project-grid-1',
                                'type' => 'ProjectGrid',
                                'props' => [
                                    'items' => [],
                                    'filters' => ['category'],
                                    'aria_label' => 'Portfolio project grid',
                                ],
                                'prompt_metadata' => ['maxLength' => 150, 'readingLevel' => 8],
                            ],
                            [
                                'id' => 'project-detail-template-1',
                                'type' => 'ProjectDetailTemplate',
                                'props' => [
                                    'template' => 'default',
                                    'aria_label' => 'Project detail template',
                                ],
                                'prompt_metadata' => ['maxLength' => 200, 'readingLevel' => 8],
                            ],
                        ],
                    ],
                ],
                [
                    'page_type' => 'about',
                    'slug' => 'about',
                    'title' => 'About Us',
                    'meta_description' => 'Learn about [Business Name] - Our creative journey.',
                    'seo_data' => [
                        'canonical' => '/about',
                        'robots' => 'index,follow',
                        'schema' => ['Person' => true, 'Organization' => true],
                        'keywords' => ['[business] about', '[business] creative [location]'],
                    ],
                    'page_structure' => [
                        'version' => '1.0',
                        'components' => [
                            [
                                'id' => 'bio-1',
                                'type' => 'Bio',
                                'props' => [
                                    'title' => 'Our Story',
                                    'content' => '',
                                    'aria_label' => 'Creative bio',
                                ],
                                'prompt_metadata' => ['maxLength' => 300, 'readingLevel' => 8],
                            ],
                            [
                                'id' => 'awards-1',
                                'type' => 'Awards',
                                'props' => [
                                    'items' => [],
                                    'aria_label' => 'Awards and recognition',
                                ],
                                'prompt_metadata' => ['maxLength' => 100, 'readingLevel' => 8],
                            ],
                            [
                                'id' => 'cta-3',
                                'type' => 'CTA',
                                'props' => [
                                    'text' => 'Contact Us',
                                    'link' => '/contact',
                                    'aria_label' => 'Call to action',
                                ],
                                'prompt_metadata' => ['maxLength' => 30, 'readingLevel' => 8],
                            ],
                        ],
                    ],
                ],
                [
                    'page_type' => 'contact',
                    'slug' => 'contact',
                    'title' => 'Contact Us',
                    'meta_description' => 'Get in touch with [Business Name] for creative inquiries.',
                    'seo_data' => [
                        'canonical' => '/contact',
                        'robots' => 'index,follow',
                        'schema' => ['LocalBusiness' => true],
                        'keywords' => ['[business] contact [location]', '[business] inquiry'],
                    ],
                    'page_structure' => [
                        'version' => '1.0',
                        'components' => [
                            [
                                'id' => 'inquiry-form-1',
                                'type' => 'InquiryForm',
                                'props' => [
                                    'fields' => ['name', 'email', 'project_type', 'message'],
                                    'aria_label' => 'Inquiry form',
                                ],
                                'prompt_metadata' => ['maxLength' => 50, 'readingLevel' => 8],
                            ],
                            [
                                'id' => 'social-links-1',
                                'type' => 'SocialLinks',
                                'props' => [
                                    'links' => [],
                                    'aria_label' => 'Social media links',
                                ],
                                'prompt_metadata' => ['maxLength' => 50, 'readingLevel' => 8],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'default' => [
            'name' => 'Default (Catch-All)',
            'description' => 'Minimal flow for generic use cases.',
            'pages' => [
                [
                    'page_type' => 'home',
                    'slug' => '/',
                    'title' => 'Home',
                    'meta_description' => 'Welcome to [Business Name] - [Business Description].',
                    'seo_data' => [
                        'canonical' => '/',
                        'robots' => 'index,follow',
                        'schema' => ['LocalBusiness' => true],
                        'keywords' => ['[business] [location]', '[business] services'],
                    ],
                    'page_structure' => [
                        'version' => '1.0',
                        'components' => [
                            [
                                'id' => 'hero-1',
                                'type' => 'Hero',
                                'props' => [
                                    'headline' => '[Business Name] - [Business Tagline]',
                                    'sub' => 'Your trusted partner in [Location].',
                                    'cta' => 'Learn More',
                                    'aria_label' => 'Main hero section',
                                ],
                                'prompt_metadata' => ['maxLength' => 50, 'readingLevel' => 8],
                            ],
                            [
                                'id' => 'services-overview-1',
                                'type' => 'ServicesOverview',
                                'props' => [
                                    'items' => [],
                                    'aria_label' => 'Services or products overview',
                                ],
                                'prompt_metadata' => ['maxLength' => 150, 'readingLevel' => 8],
                            ],
                            [
                                'id' => 'testimonials-1',
                                'type' => 'Testimonials',
                                'props' => [
                                    'source' => 'manual',
                                    'items' => [],
                                    'aria_label' => 'Customer testimonials',
                                ],
                                'prompt_metadata' => ['maxLength' => 150, 'readingLevel' => 8],
                            ],
                            [
                                'id' => 'cta-1',
                                'type' => 'CTA',
                                'props' => [
                                    'text' => 'Get in Touch',
                                    'link' => '/contact',
                                    'aria_label' => 'Call to action',
                                ],
                                'prompt_metadata' => ['maxLength' => 30, 'readingLevel' => 8],
                            ],
                        ],
                    ],
                ],
                [
                    'page_type' => 'about',
                    'slug' => 'about',
                    'title' => 'About Us',
                    'meta_description' => 'Learn about [Business Name] - Our story and mission.',
                    'seo_data' => [
                        'canonical' => '/about',
                        'robots' => 'index,follow',
                        'schema' => ['Organization' => true],
                        'keywords' => ['[business] about', '[business] mission [location]'],
                    ],
                    'page_structure' => [
                        'version' => '1.0',
                        'components' => [
                            [
                                'id' => 'story-1',
                                'type' => 'Story',
                                'props' => [
                                    'title' => 'Our Story',
                                    'content' => '',
                                    'aria_label' => 'Business story',
                                ],
                                'prompt_metadata' => ['maxLength' => 300, 'readingLevel' => 8],
                            ],
                            [
                                'id' => 'cta-2',
                                'type' => 'CTA',
                                'props' => [
                                    'text' => 'Contact Us',
                                    'link' => '/contact',
                                    'aria_label' => 'Call to action',
                                ],
                                'prompt_metadata' => ['maxLength' => 30, 'readingLevel' => 8],
                            ],
                        ],
                    ],
                ],
                [
                    'page_type' => 'contact',
                    'slug' => 'contact',
                    'title' => 'Contact Us',
                    'meta_description' => 'Get in touch with [Business Name] for inquiries.',
                    'seo_data' => [
                        'canonical' => '/contact',
                        'robots' => 'index,follow',
                        'schema' => ['LocalBusiness' => true],
                        'keywords' => ['[business] contact [location]', '[business] inquiry'],
                    ],
                    'page_structure' => [
                        'version' => '1.0',
                        'components' => [
                            [
                                'id' => 'form-1',
                                'type' => 'ContactForm',
                                'props' => [
                                    'fields' => ['name', 'email', 'message'],
                                    'aria_label' => 'Contact form',
                                ],
                                'prompt_metadata' => ['maxLength' => 50, 'readingLevel' => 8],
                            ],
                            [
                                'id' => 'map-1',
                                'type' => 'Map',
                                'props' => [
                                    'location' => '[Business Address]',
                                    'aria_label' => 'Business location map',
                                ],
                                'prompt_metadata' => [], // No AI content needed
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
];
?>