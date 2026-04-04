# Stock Dashboard (米国株ポートフォリオ管理システム)

## 概要

サーバー側に個人情報や資産データを一切保存せず、ブラウザの `localStorage` を活用して銘柄の管理や損益計算を行うSPAベースのポートフォリオ管理システムです。
バックエンドのLaravelは、APIキーを秘匿しつつ最新の株価・為替データを取得するためのプロキシサーバーとして動作します。

## 主な機能

- **銘柄管理**: ティッカーシンボル、取得単価（USD）、保有数量の登録・編集・削除
- **株価・為替データ取得**: 外部APIを利用した複数銘柄の最新株価・為替レートの取得（サーバー側で銘柄ごとに約2時間キャッシュ）
- **損益表示**: 銘柄ごとの現在価格、評価額、評価損益、損益率の計算
- **ポートフォリオ集計**: ポートフォリオ全体の総評価額、総損益の合算表示
- **為替対応**: JPY(円)ベース・USD(ドル)ベースの両方での資産算出・表示
- **資産配分の可視化**: Chart.jsを用いた円グラフ(Pie Chart)等でのポートフォリオ内訳表示
- **履歴管理**: `localStorage` を活用した簡易的な資産推移の保持とグラフ表示

## 技術スタック

- **フロントエンド**: HTML (Blade), Tailwind CSS（Vite / npm でビルド）, Alpine.js, Chart.js（後者2つは Blade レイアウトから CDN 読み込み。`package.json` の npm 依存には含まれない）
- **バックエンド**: PHP, Laravel 12 (APIプロキシとしてのみ使用するStateless構成)
- **外部API**: Financial Modeling Prep API
- **開発用コンテナ**: Laravel Sail（`compose.yaml`、`docker/` 配下。利用は任意）

## ローカル環境の構築

### 前提環境

- PHP 8.2 以上（`composer.json` の要件）
- Composer
- Node.js / npm（Vite 7 利用のため、Node.js 20 以上を推奨）

以下の手順で開発環境をセットアップできます。

```bash
# 1. リポジトリのクローン
git clone <repository_url>
cd stock-app

# 2. PHPおよびNode.jsの依存関係をインストール
composer install
npm install

# 3. 環境変数の設定準備
cp .env.example .env

# 4. アプリケーションキーの生成
php artisan key:generate

# 5. 開発サーバーの起動（Laravel + Vite などをまとめて起動）
composer run dev
```

別ターミナルで分けて動かす場合は、`php artisan serve` と `npm run dev` を同時に起動してください。

### Laravel Sail を使う場合

Docker で開発する場合の例です。

```bash
./vendor/bin/sail up -d
./vendor/bin/sail npm run dev
```

手順 2〜4（依存関係のインストールや `key:generate`）は、ホストで実行しても `./vendor/bin/sail composer install` や `./vendor/bin/sail artisan key:generate` のようにコンテナ内で実行しても構いません。詳細は [Laravel Sail](https://laravel.com/docs/sail) を参照してください。

### データベース・キャッシュについて

`.env.example` では `CACHE_STORE=file`・`SESSION_DRIVER=file`・`QUEUE_CONNECTION=sync` を既定にしており、**このアプリの動作にマイグレーションは不要**です。`CACHE_STORE` や `SESSION_DRIVER` を `database` に変更した場合は、Laravel の要件どおり `php artisan migrate` でテーブルを作成してください。

### 必要な環境変数設定
APIからデータを取得するために、`.env` ファイルにFinancial Modeling PrepのAPIキーを設定してください。

```env
FMP_API_KEY=your_financial_modeling_prep_api_key
```

## 非機能・セキュリティ

- **パフォーマンス**: 初回表示は即時に `localStorage` のデータを反映し、API通信はバックグラウンドの非同期で行います。
- **可用性**: 銘柄や前回までに取得した株価・為替などは `localStorage` に残るため、API が一時的に失敗しても多くの場合は直前までの表示を維持できます。初回利用時など未取得のときは、取得単価ベースの表示にとどまることがあります。
- **セキュリティ**: 個人のポートフォリオ情報はサーバーに送信・保存されません。外部APIを叩くためのトークンはサーバー側（Laravel）で秘匿化しています。

## 開発対象外（スコープ外）の機能

本プロジェクトでは以下の機能はスコープ外としています。
- ユーザー認証（ログイン機能）
- サーバー（DB等）でのデータ永続化
- WebSocket等による高頻度なリアルタイム更新
- 日本株など米国株以外の銘柄の取得
