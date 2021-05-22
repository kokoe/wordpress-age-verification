# wordpress-age-verification

WordPress で利用することを想定した、Cookie を利用した年齢認証。

- Requires at least: 5.3
- Requires PHP: 5.6

## 設置手順

### 1. 固定ページより、以下内容で年齢認証ページを作成。

- パーマリンク・URL スラッグ： `gate`
- ページ属性・テンプレート： `年齢認証`

### 2. 年齢認証の対象になるページに以下コードを設定

```php
Age_Verification::verify();
```

## LICENSE

MIT
