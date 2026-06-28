# CampusEventHub Web Important Functions Map

This document highlights the most important web functions and where they are located.

## 1) Main Route Entry Points

- Web routes definition: [routes/web.php](routes/web.php)
- API routes definition: [routes/api.php](routes/api.php)

Key route anchors:

- API health check: [routes/api.php](routes/api.php#L17)
- API auth register and login: [routes/api.php](routes/api.php#L53), [routes/api.php](routes/api.php#L54)
- API event feed and detail: [routes/api.php](routes/api.php#L58), [routes/api.php](routes/api.php#L60)
- API recommendation endpoints: [routes/api.php](routes/api.php#L96), [routes/api.php](routes/api.php#L97)
- API join and leave event: [routes/api.php](routes/api.php#L103), [routes/api.php](routes/api.php#L104)
- API Telegram webhook: [routes/api.php](routes/api.php#L72)
- Web payment endpoints: [routes/web.php](routes/web.php#L71), [routes/web.php](routes/web.php#L74)
- Web social publishing endpoints: [routes/web.php](routes/web.php#L98), [routes/web.php](routes/web.php#L100)
- Web Instagram scheduler endpoints: [routes/web.php](routes/web.php#L105), [routes/web.php](routes/web.php#L108)
- Web AI description endpoints: [routes/web.php](routes/web.php#L151), [routes/web.php](routes/web.php#L152)

## 2) Authentication and Profile (API)

- Register mobile/API user: [app/Http/Controllers/Api/AuthController.php](app/Http/Controllers/Api/AuthController.php#L13)
- Login mobile/API user: [app/Http/Controllers/Api/AuthController.php](app/Http/Controllers/Api/AuthController.php#L48)
- Update profile (with photo upload): [app/Http/Controllers/Api/AuthController.php](app/Http/Controllers/Api/AuthController.php#L78)
- Get user joined events: [app/Http/Controllers/Api/AuthController.php](app/Http/Controllers/Api/AuthController.php#L159)
- Get user orders: [app/Http/Controllers/Api/AuthController.php](app/Http/Controllers/Api/AuthController.php#L193)

## 3) Event Feed, Join, and Like (API)

- Event listing with filtering and status shaping: [app/Http/Controllers/Api/EventController.php](app/Http/Controllers/Api/EventController.php#L78)
- Event detail endpoint: [app/Http/Controllers/Api/EventController.php](app/Http/Controllers/Api/EventController.php#L132)
- Event search endpoint: [app/Http/Controllers/Api/EventController.php](app/Http/Controllers/Api/EventController.php#L159)
- Join event transaction flow: [app/Http/Controllers/Api/EventController.php](app/Http/Controllers/Api/EventController.php#L185)
- Leave event flow: [app/Http/Controllers/Api/EventController.php](app/Http/Controllers/Api/EventController.php#L334)
- Join status endpoint: [app/Http/Controllers/Api/EventController.php](app/Http/Controllers/Api/EventController.php#L431)
- Like event endpoint: [app/Http/Controllers/Api/EventController.php](app/Http/Controllers/Api/EventController.php#L488)
- Unlike event endpoint: [app/Http/Controllers/Api/EventController.php](app/Http/Controllers/Api/EventController.php#L567)
- Like status endpoint: [app/Http/Controllers/Api/EventController.php](app/Http/Controllers/Api/EventController.php#L629)

## 4) Recommendation Engine

- API recommendation response endpoint: [app/Http/Controllers/Api/RecommendationController.php](app/Http/Controllers/Api/RecommendationController.php#L29)
- Similar events API endpoint: [app/Http/Controllers/Api/RecommendationController.php](app/Http/Controllers/Api/RecommendationController.php#L65)
- User likes endpoint: [app/Http/Controllers/Api/RecommendationController.php](app/Http/Controllers/Api/RecommendationController.php#L168)

Core recommendation logic in service layer:

- Personalized recommendation algorithm: [app/Services/ContentBasedFilteringService.php](app/Services/ContentBasedFilteringService.php#L136)
- Similar event scoring: [app/Services/ContentBasedFilteringService.php](app/Services/ContentBasedFilteringService.php#L206)
- Event similarity scoring function: [app/Services/ContentBasedFilteringService.php](app/Services/ContentBasedFilteringService.php#L42)
- User profile construction from likes: [app/Services/ContentBasedFilteringService.php](app/Services/ContentBasedFilteringService.php#L68)

## 5) Payment Pipeline (Web)

- Create ToyyibPay bill for checkout: [app/Http/Controllers/Web/PaymentController.php](app/Http/Controllers/Web/PaymentController.php#L52)
- Multi-item checkout path: [app/Http/Controllers/Web/PaymentController.php](app/Http/Controllers/Web/PaymentController.php#L151)
- Payment callback handler from gateway: [app/Http/Controllers/Web/PaymentController.php](app/Http/Controllers/Web/PaymentController.php#L265)
- Signed return handler after payment: [app/Http/Controllers/Web/PaymentController.php](app/Http/Controllers/Web/PaymentController.php#L320)
- Success processing and cart cleanup: [app/Http/Controllers/Web/PaymentController.php](app/Http/Controllers/Web/PaymentController.php#L360), [app/Http/Controllers/Web/PaymentController.php](app/Http/Controllers/Web/PaymentController.php#L383)
- Order generation from successful payment: [app/Http/Controllers/Web/PaymentController.php](app/Http/Controllers/Web/PaymentController.php#L441)
- Gateway status verification safeguard: [app/Http/Controllers/Web/PaymentController.php](app/Http/Controllers/Web/PaymentController.php#L684)

## 6) Social Publishing and Instagram (Web)

- Social media dashboard: [app/Http/Controllers/Web/InstagramController.php](app/Http/Controllers/Web/InstagramController.php#L34)
- Publish event to Instagram: [app/Http/Controllers/Web/InstagramController.php](app/Http/Controllers/Web/InstagramController.php#L144)
- Publish event to Facebook: [app/Http/Controllers/Web/InstagramController.php](app/Http/Controllers/Web/InstagramController.php#L211)
- Publish event to all platforms: [app/Http/Controllers/Web/InstagramController.php](app/Http/Controllers/Web/InstagramController.php#L266)
- Schedule Instagram post: [app/Http/Controllers/Web/InstagramController.php](app/Http/Controllers/Web/InstagramController.php#L529)
- Cancel scheduled post: [app/Http/Controllers/Web/InstagramController.php](app/Http/Controllers/Web/InstagramController.php#L563)
- Repost now: [app/Http/Controllers/Web/InstagramController.php](app/Http/Controllers/Web/InstagramController.php#L608)
- Schedule repost: [app/Http/Controllers/Web/InstagramController.php](app/Http/Controllers/Web/InstagramController.php#L617)

Underlying integration services:

- Core Instagram image post call: [app/Services/InstagramService.php](app/Services/InstagramService.php#L53)
- Instagram post with custom club credentials: [app/Services/InstagramService.php](app/Services/InstagramService.php#L109)
- Scheduled queue processor: [app/Services/ScheduledInstagramPostService.php](app/Services/ScheduledInstagramPostService.php#L32)
- Post one scheduled event: [app/Services/ScheduledInstagramPostService.php](app/Services/ScheduledInstagramPostService.php#L120)

## 7) Telegram Integration (API + Service)

- Telegram webhook ingress endpoint: [app/Http/Controllers/Api/TelegramController.php](app/Http/Controllers/Api/TelegramController.php#L23)
- Link app user to Telegram chat: [app/Http/Controllers/Api/TelegramController.php](app/Http/Controllers/Api/TelegramController.php#L51)
- Update Telegram notification preferences: [app/Http/Controllers/Api/TelegramController.php](app/Http/Controllers/Api/TelegramController.php#L121)
- Fetch this-week events for Telegram users: [app/Http/Controllers/Api/TelegramController.php](app/Http/Controllers/Api/TelegramController.php#L198)

Core bot behavior:

- Main Telegram update dispatcher: [app/Services/TelegramBotService.php](app/Services/TelegramBotService.php#L108)
- Message sender function: [app/Services/TelegramBotService.php](app/Services/TelegramBotService.php#L212)
- Scheduled notifications sender: [app/Services/TelegramBotService.php](app/Services/TelegramBotService.php#L634)
- Weekly recommendation push: [app/Services/TelegramBotService.php](app/Services/TelegramBotService.php#L685)

## 8) AI Content Generation

- Web endpoint for generating event descriptions: [routes/web.php](routes/web.php#L151)
- Web endpoint for tweaking descriptions: [routes/web.php](routes/web.php#L152)
- Generate description from prompt builder + Gemini call: [app/Services/GeminiService.php](app/Services/GeminiService.php#L27)
- Tweak existing copy style: [app/Services/GeminiService.php](app/Services/GeminiService.php#L33)
- Low-level Gemini API call wrapper: [app/Services/GeminiService.php](app/Services/GeminiService.php#L47)

## 9) Background Automation and Schedules

- Global scheduler definition: [app/Console/Kernel.php](app/Console/Kernel.php#L13)
- Scheduled Instagram posting command runner: [app/Console/Commands/ProcessScheduledInstagramPosts.php](app/Console/Commands/ProcessScheduledInstagramPosts.php#L41)
- Telegram weekly recommendation command runner: [app/Console/Commands/SendTelegramWeeklyRecommendations.php](app/Console/Commands/SendTelegramWeeklyRecommendations.php#L29)
- Instagram metrics sync command runner: [app/Console/Commands/SyncInstagramMetrics.php](app/Console/Commands/SyncInstagramMetrics.php#L39)

## 10) Suggested Reading Order

If you want to understand the system quickly, read in this order:

1. [routes/api.php](routes/api.php)
2. [app/Http/Controllers/Api/AuthController.php](app/Http/Controllers/Api/AuthController.php)
3. [app/Http/Controllers/Api/EventController.php](app/Http/Controllers/Api/EventController.php)
4. [app/Http/Controllers/Api/RecommendationController.php](app/Http/Controllers/Api/RecommendationController.php)
5. [app/Services/ContentBasedFilteringService.php](app/Services/ContentBasedFilteringService.php)
6. [app/Http/Controllers/Web/PaymentController.php](app/Http/Controllers/Web/PaymentController.php)
7. [app/Http/Controllers/Web/InstagramController.php](app/Http/Controllers/Web/InstagramController.php)
8. [app/Services/TelegramBotService.php](app/Services/TelegramBotService.php)
