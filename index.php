<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GoCheck - Your Health Monitoring Partner</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#2F4F2F',
                        secondary: '#BFB1A4'
                    },
                    borderRadius: {
                        'none': '0px',
                        'sm': '4px',
                        DEFAULT: '8px',
                        'md': '12px',
                        'lg': '16px',
                        'xl': '20px',
                        '2xl': '24px',
                        '3xl': '32px',
                        'full': '9999px',
                        'button': '8px'
                    }
                }
            }
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/echarts/5.5.0/echarts.min.js"></script>
    <style>
        :where([class^="ri-"])::before { content: "\f3c2"; }
        .chart-container { min-height: 300px; }
    </style>
</head>
<body class="bg-white">
    <nav class="bg-primary text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center">
                    <span class="font-['Pacifico'] text-2xl">GoCheck</span>
                    <div class="ml-10 flex items-baseline space-x-4">
                        <a href="/" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-secondary/20">Home</a>
                        <a href="/services" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-secondary/20">Services</a>
                        <a href="/health-tracking" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-secondary/20">Health Tracking</a>
                        <a href="/diet-plans" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-secondary/20">Diet Plans</a>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <a href="login.php" class="!rounded-button bg-secondary/20 px-4 py-2 text-sm font-medium hover:bg-secondary/30 whitespace-nowrap">Login</a>
                    <a href="register.php" class="!rounded-button bg-secondary px-4 py-2 text-sm font-medium hover:bg-secondary/80 whitespace-nowrap">Register</a>
                </div>
            </div>
        </div>
    </nav>

    <main>
        <section class="relative bg-gradient-to-r from-primary/10 to-transparent overflow-hidden">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24">
                <div class="grid grid-cols-2 gap-8 items-center">
                    <div>
                        <h1 class="text-5xl font-bold text-gray-900 mb-6">Monitor Your Health Journey with GoCheck</h1>
                        <p class="text-xl text-gray-600 mb-8">Track your vital health metrics, receive personalized insights, and take control of your well-being with our comprehensive health monitoring system.</p>
                        <a href="/start-monitoring" class="!rounded-button bg-primary text-white px-8 py-4 text-lg font-medium hover:bg-primary/90 whitespace-nowrap">Start Monitoring Now</a>
                    </div>
                    <div>
                        <img src="https://public.readdy.ai/ai/img_res/5614505390844ba1f0868055e5704440.jpg" alt="Health Dashboard" class="rounded-lg shadow-xl">
                    </div>
                </div>
            </div>
        </section>

        <section class="py-16 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-3xl font-bold text-center mb-12">Track Your Health Metrics</h2>
                <div class="grid grid-cols-2 gap-8">
                    <div class="bg-white rounded-lg shadow-lg p-6">
                        <div class="mb-6">
                            <h3 class="text-xl font-semibold mb-4">Upload Health Data</h3>
                            <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center">
                                <i class="ri-upload-cloud-line ri-3x text-gray-400"></i>
                                <p class="mt-2 text-sm text-gray-600">Drag and drop your health records here or</p>
                                <a href="/upload" class="!rounded-button mt-2 bg-primary text-white px-4 py-2 text-sm font-medium hover:bg-primary/90 whitespace-nowrap">Browse Files</a>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="p-4 bg-gray-50 rounded-lg">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-sm font-medium">Urea</span>
                                    <span class="text-sm text-gray-600">15.2 mg/dL</span>
                                </div>
                                <div class="h-2 bg-gray-200 rounded-full">
                                    <div class="h-2 bg-primary rounded-full" style="width: 75%"></div>
                                </div>
                            </div>
                            <div class="p-4 bg-gray-50 rounded-lg">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-sm font-medium">Creatinine</span>
                                    <span class="text-sm text-gray-600">0.9 mg/dL</span>
                                </div>
                                <div class="h-2 bg-gray-200 rounded-full">
                                    <div class="h-2 bg-primary rounded-full" style="width: 60%"></div>
                                </div>
                            </div>
                            <div class="p-4 bg-gray-50 rounded-lg">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-sm font-medium">Uric Acid</span>
                                    <span class="text-sm text-gray-600">5.7 mg/dL</span>
                                </div>
                                <div class="h-2 bg-gray-200 rounded-full">
                                    <div class="h-2 bg-primary rounded-full" style="width: 85%"></div>
                                </div>
                            </div>
                            <div class="p-4 bg-gray-50 rounded-lg">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-sm font-medium">Calcium</span>
                                    <span class="text-sm text-gray-600">9.5 mg/dL</span>
                                </div>
                                <div class="h-2 bg-gray-200 rounded-full">
                                    <div class="h-2 bg-primary rounded-full" style="width: 70%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow-lg p-6">
                        <h3 class="text-xl font-semibold mb-4">Health Trends</h3>
                        <div id="healthChart" class="chart-container"></div>
                    </div>
                </div>
            </div>
        </section>

        <section class="py-16 bg-gray-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-3xl font-bold text-center mb-12">Personalized Diet Plans</h2>
                <div class="grid grid-cols-3 gap-8">
                    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                        <img src="https://public.readdy.ai/ai/img_res/0e34f7d0f67422dd60dba936ce0e4a39.jpg" alt="Balanced Diet" class="w-full h-48 object-cover">
                        <div class="p-6">
                            <h3 class="text-xl font-semibold mb-2">Balanced Diet</h3>
                            <p class="text-gray-600 mb-4">Perfect balance of proteins, carbs, and healthy fats for optimal health.</p>
                            <a href="/diet/balanced" class="!rounded-button w-full bg-primary text-white px-4 py-2 text-sm font-medium hover:bg-primary/90 whitespace-nowrap">View Plan</a>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                        <img src="https://public.readdy.ai/ai/img_res/fcad4db247759c581a68a925011b08ea.jpg" alt="Plant-Based Diet" class="w-full h-48 object-cover">
                        <div class="p-6">
                            <h3 class="text-xl font-semibold mb-2">Plant-Based Diet</h3>
                            <p class="text-gray-600 mb-4">Nutrient-rich plant-based meals for a healthier lifestyle.</p>
                            <a href="/diet/plant-based" class="!rounded-button w-full bg-primary text-white px-4 py-2 text-sm font-medium hover:bg-primary/90 whitespace-nowrap">View Plan</a>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                        <img src="https://public.readdy.ai/ai/img_res/24f837a4c147cd5671d7d51d305f078b.jpg" alt="Mediterranean Diet" class="w-full h-48 object-cover">
                        <div class="p-6">
                            <h3 class="text-xl font-semibold mb-2">Mediterranean Diet</h3>
                            <p class="text-gray-600 mb-4">Heart-healthy meals inspired by Mediterranean cuisine.</p>
                            <a href="/diet/mediterranean" class="!rounded-button w-full bg-primary text-white px-4 py-2 text-sm font-medium hover:bg-primary/90 whitespace-nowrap">View Plan</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="py-16 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-3xl font-bold text-center mb-12">Why Choose GoCheck?</h2>
                <div class="grid grid-cols-4 gap-8">
                    <div class="text-center">
                        <div class="w-16 h-16 mx-auto mb-4 bg-primary/10 rounded-full flex items-center justify-center">
                            <i class="ri-heart-pulse-line ri-2x text-primary"></i>
                        </div>
                        <h3 class="text-lg font-semibold mb-2">Real-time Monitoring</h3>
                        <p class="text-gray-600">Track your health metrics in real-time with accurate measurements</p>
                    </div>
                    <div class="text-center">
                        <div class="w-16 h-16 mx-auto mb-4 bg-primary/10 rounded-full flex items-center justify-center">
                            <i class="ri-notification-line ri-2x text-primary"></i>
                        </div>
                        <h3 class="text-lg font-semibold mb-2">Smart Alerts</h3>
                        <p class="text-gray-600">Receive instant notifications about important health changes</p>
                    </div>
                    <div class="text-center">
                        <div class="w-16 h-16 mx-auto mb-4 bg-primary/10 rounded-full flex items-center justify-center">
                            <i class="ri-lock-line ri-2x text-primary"></i>
                        </div>
                        <h3 class="text-lg font-semibold mb-2">Secure Data</h3>
                        <p class="text-gray-600">Your health data is protected with enterprise-grade security</p>
                    </div>
                    <div class="text-center">
                        <div class="w-16 h-16 mx-auto mb-4 bg-primary/10 rounded-full flex items-center justify-center">
                            <i class="ri-customer-service-line ri-2x text-primary"></i>
                        </div>
                        <h3 class="text-lg font-semibold mb-2">24/7 Support</h3>
                        <p class="text-gray-600">Expert support available around the clock for your needs</p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer class="bg-primary text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-4 gap-8">
                <div>
                    <span class="font-['Pacifico'] text-2xl">GoCheck</span>
                    <p class="mt-4 text-sm text-gray-300">Your trusted partner in health monitoring and wellness journey.</p>
                </div>
                <div>
                    <h4 class="text-lg font-semibold mb-4">Quick Links</h4>
                    <ul class="space-y-2">
                        <li><a href="/about" class="text-sm text-gray-300 hover:text-white">About Us</a></li>
                        <li><a href="/services" class="text-sm text-gray-300 hover:text-white">Services</a></li>
                        <li><a href="/contact" class="text-sm text-gray-300 hover:text-white">Contact</a></li>
                        <li><a href="/privacy" class="text-sm text-gray-300 hover:text-white">Privacy Policy</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-lg font-semibold mb-4">Contact Us</h4>
                    <ul class="space-y-2">
                        <li class="text-sm text-gray-300">Email: support@gocheck.com</li>
                        <li class="text-sm text-gray-300">Phone: +1 (555) 123-4567</li>
                        <li class="text-sm text-gray-300">Address: 123 Health Street, Medical District, NY 10001</li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-lg font-semibold mb-4">Newsletter</h4>
                    <div class="flex gap-2">
                        <input type="email" placeholder="Enter your email" class="flex-1 px-4 py-2 rounded-button text-gray-900 text-sm">
                        <a href="/subscribe" class="!rounded-button bg-secondary px-4 py-2 text-sm font-medium hover:bg-secondary/80 whitespace-nowrap">Subscribe</a>
                    </div>
                </div>
            </div>
            <div class="mt-8 pt-8 border-t border-gray-700">
                <p class="text-center text-sm text-gray-300">© 2025 GoCheck. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        const healthChart = echarts.init(document.getElementById('healthChart'));
        const option = {
            animation: false,
            tooltip: {
                trigger: 'axis',
                backgroundColor: 'rgba(255, 255, 255, 0.9)',
                textStyle: {
                    color: '#1f2937'
                }
            },
            legend: {
                data: ['Urea', 'Creatinine', 'Uric Acid', 'Calcium'],
                textStyle: {
                    color: '#1f2937'
                }
            },
            grid: {
                top: 60,
                right: 20,
                bottom: 40,
                left: 50
            },
            xAxis: {
                type: 'category',
                data: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                axisLine: {
                    lineStyle: {
                        color: '#1f2937'
                    }
                }
            },
            yAxis: {
                type: 'value',
                axisLine: {
                    lineStyle: {
                        color: '#1f2937'
                    }
                }
            },
            series: [
                {
                    name: 'Urea',
                    type: 'line',
                    smooth: true,
                    data: [14, 15.2, 14.8, 15.5, 15.2, 14.9],
                    lineStyle: {
                        color: 'rgba(87, 181, 231, 1)'
                    },
                    areaStyle: {
                        color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [
                            {
                                offset: 0,
                                color: 'rgba(87, 181, 231, 0.3)'
                            },
                            {
                                offset: 1,
                                color: 'rgba(87, 181, 231, 0.1)'
                            }
                        ])
                    }
                },
                {
                    name: 'Creatinine',
                    type: 'line',
                    smooth: true,
                    data: [0.8, 0.9, 0.85, 0.95, 0.9, 0.88],
                    lineStyle: {
                        color: 'rgba(141, 211, 199, 1)'
                    },
                    areaStyle: {
                        color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [
                            {
                                offset: 0,
                                color: 'rgba(141, 211, 199, 0.3)'
                            },
                            {
                                offset: 1,
                                color: 'rgba(141, 211, 199, 0.1)'
                            }
                        ])
                    }
                },
                {
                    name: 'Uric Acid',
                    type: 'line',
                    smooth: true,
                    data: [5.5, 5.7, 5.6, 5.8, 5.7, 5.6],
                    lineStyle: {
                        color: 'rgba(251, 191, 114, 1)'
                    },
                    areaStyle: {
                        color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [
                            {
                                offset: 0,
                                color: 'rgba(251, 191, 114, 0.3)'
                            },
                            {
                                offset: 1,
                                color: 'rgba(251, 191, 114, 0.1)'
                            }
                        ])
                    }
                },
                {
                    name: 'Calcium',
                    type: 'line',
                    smooth: true,
                    data: [9.2, 9.5, 9.4, 9.6, 9.5, 9.3],
                    lineStyle: {
                        color: 'rgba(252, 141, 98, 1)'
                    },
                    areaStyle: {
                        color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [
                            {
                                offset: 0,
                                color: 'rgba(252, 141, 98, 0.3)'
                            },
                            {
                                offset: 1,
                                color: 'rgba(252, 141, 98, 0.1)'
                            }
                        ])
                    }
                }
            ]
        };
        healthChart.setOption(option);

        window.addEventListener('resize', function() {
            healthChart.resize();
        });
    </script>
</body>
</html>