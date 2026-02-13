# Music Platform - User Module with OAuth Authentication

## Overview

This is a Spotify-like music platform backend with a complete User module implementing OAuth authentication using Domain-Driven Design (DDD) architecture.

## Features

### OAuth Authentication
- **Google OAuth Integration** - Login with Google account
- **Facebook OAuth Integration** - Login with Facebook account
- **JWT Token Management** - Secure token-based authentication
- **Token Refresh** - Automatic token renewal
- **Logout** - Secure token revocation

### User Management
- **Profile Management** - View and update user profile
- **Role Management** - Support for multiple user roles
- **Avatar Upload** - Profile picture management
- **User Lookup** - Get user information by ID

## Architecture

### Domain-Driven Design (DDD)
```
src/User/
├── Controller/          # API Endpoints
├── Service/
│   ├── Interface/       # Service Interfaces
│   └── Impl/          # Service Implementations
├── Repository/
│   ├── Interface/       # Repository Interfaces
│   └── Repository/     # Repository Implementations
├── DTO/               # Data Transfer Objects
├── Mapper/            # Entity-DTO Mapping
└── Entity/            # Domain Entities
```

### Key Components

#### Entities
- **User** - Core user entity with JWT authentication support
- **OAuthProvider** - OAuth provider information (Google/Facebook)

#### DTOs
- **UserDTO** - User data transfer object
- **OAuthResponseDTO** - OAuth login response with JWT tokens
- **OAuthUserDataDTO** - OAuth provider user data

#### Services
- **UserService** - User business logic
- **JWTTokenService** - JWT token generation and validation
- **OAuthTokenService** - OAuth token validation

#### Controllers
- **OAuthController** - Authentication endpoints
- **UserController** - User profile management

## API Endpoints

### Authentication (`/api/auth`)
- `POST /api/auth/google` - Google OAuth login
- `POST /api/auth/facebook` - Facebook OAuth login
- `POST /api/auth/refresh` - Refresh JWT token
- `POST /api/auth/logout` - Logout and revoke token

### User Management (`/api/users`)
- `GET /api/users/me` - Get current user profile
- `PUT /api/users/me` - Update current user profile
- `GET /api/users/{id}` - Get user by ID (public view)

## Testing

### Unit Tests
- **UserService Tests** - Complete OAuth authentication flow testing
- **UserMapper Tests** - Entity-DTO conversion testing
- **OAuthMapper Tests** - OAuth data mapping testing
- **OAuthTokenService Tests** - Token validation testing

### API Testing
- **Postman Collection** - Complete API test collection
- Location: `public/oauth-test-collection.json`

## Configuration

### Environment Variables
```env
JWT_SECRET_KEY=your-super-secret-jwt-key-change-this-in-production-32-chars-min
JWT_TOKEN_LIFETIME=3600
JWT_REFRESH_TOKEN_LIFETIME=2592000
```

### JWT Configuration
- **Access Token Lifetime**: 1 hour
- **Refresh Token Lifetime**: 30 days
- **Algorithm**: HS256
- **Token Revocation**: Cache-based

## Security Features

### JWT Security
- **Signed Tokens** - HMAC-SHA256 signing
- **Token Expiration** - Automatic token expiry
- **Token Refresh** - Secure token renewal
- **Token Revocation** - Immediate logout capability

### OAuth Security
- **Token Validation** - Provider token verification
- **Required Fields** - Email and ID validation
- **Error Handling** - Comprehensive error responses

## Installation & Setup

### Dependencies
```bash
composer install
```

### Database Setup
```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

### Start Development Server
```bash
php bin/console server:run
```

## Usage Examples

### Google OAuth Login
```bash
curl -X POST http://localhost:8000/api/auth/google \
  -H "Content-Type: application/json" \
  -d '{"token": "google-access-token"}'
```

### Facebook OAuth Login
```bash
curl -X POST http://localhost:8000/api/auth/facebook \
  -H "Content-Type: application/json" \
  -d '{"token": "facebook-access-token"}'
```

### Get User Profile
```bash
curl -X GET http://localhost:8000/api/users/me \
  -H "Authorization: Bearer jwt-token"
```

## Development

### Code Quality
- **SOLID Principles** - Clean architecture
- **Type Safety** - Strong typing throughout
- **Error Handling** - Comprehensive exception management
- **Testing** - High test coverage

### Git Workflow
- **Feature Branches** - Isolated development
- **Commits** - Atomic, descriptive commits
- **Testing** - Tests before merging

## Contributing

1. Follow DDD architecture
2. Write comprehensive tests
3. Use SOLID principles
4. Commit with descriptive messages
5. Ensure all tests pass

## License

Proprietary
