<?php
declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;

final class httpUtilTest extends \PHPUnit\Framework\TestCase {

	/**
	 * @return Traversable<array{string,array<string,int>,string}>
	 */
	public static function provideRateLimitDomains(): Traversable {
		$reddit = ['reddit.com' => 60];

		// Exact match
		yield ['reddit.com', $reddit, 'reddit.com'];
		// Subdomains match the configured domain
		yield ['www.reddit.com', $reddit, 'reddit.com'];
		yield ['old.reddit.com', $reddit, 'reddit.com'];
		// Case insensitive
		yield ['WWW.Reddit.COM', $reddit, 'reddit.com'];
		// No match
		yield ['example.com', $reddit, ''];
		// Must not match on a suffix that is not a subdomain boundary
		yield ['notreddit.com', $reddit, ''];
		yield ['reddit.com.evil.com', $reddit, ''];
		// No configured limits
		yield ['reddit.com', [], ''];
		// Prefer the most specific (longest) match
		yield ['api.reddit.com', ['reddit.com' => 60, 'api.reddit.com' => 5], 'api.reddit.com'];
	}

	/**
	 * @param array<string,int> $rateLimits
	 */
	#[DataProvider('provideRateLimitDomains')]
	public function testFindRateLimitDomain(string $host, array $rateLimits, string $expected): void {
		self::assertSame($expected, FreshRSS_http_Util::findRateLimitDomain($host, $rateLimits));
	}
}
