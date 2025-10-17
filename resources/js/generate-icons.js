/**
 * PWAアイコン生成スクリプト
 * faviconから各サイズのアイコンを生成します
 *
 * 使用方法:
 * 1. ImageMagickまたはGraphicsMagickをインストール
 * 2. npm install sharp
 * 3. node resources/js/generate-icons.js
 */

import sharp from 'sharp';
import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

// アイコンサイズの定義
const ICON_SIZES = [72, 96, 128, 144, 152, 192, 384, 512];

// パス設定
const INPUT_FAVICON = path.join(__dirname, '../../public/favicon.ico');
const OUTPUT_DIR = path.join(__dirname, '../../public/images/icons');

// 出力ディレクトリが存在しない場合は作成
if (!fs.existsSync(OUTPUT_DIR)) {
  fs.mkdirSync(OUTPUT_DIR, { recursive: true });
}

/**
 * faviconから指定サイズのPNGアイコンを生成
 */
async function generateIcon(size) {
  const outputPath = path.join(OUTPUT_DIR, `icon-${size}x${size}.png`);

  try {
    await sharp(INPUT_FAVICON)
      .resize(size, size, {
        kernel: sharp.kernel.lanczos3,
        fit: 'contain',
        background: { r: 255, g: 255, b: 255, alpha: 0 }
      })
      .png()
      .toFile(outputPath);

    console.log(`✓ Generated: icon-${size}x${size}.png`);
  } catch (error) {
    console.error(`✗ Failed to generate icon-${size}x${size}.png:`, error.message);
  }
}

/**
 * すべてのアイコンを生成
 */
async function generateAllIcons() {
  console.log('PWA Icon Generation');
  console.log('===================\n');
  console.log(`Input: ${INPUT_FAVICON}`);
  console.log(`Output: ${OUTPUT_DIR}\n`);

  // faviconが存在するか確認
  if (!fs.existsSync(INPUT_FAVICON)) {
    console.error('✗ Error: favicon.ico not found!');
    console.error(`  Expected location: ${INPUT_FAVICON}`);
    process.exit(1);
  }

  // 各サイズのアイコンを生成
  for (const size of ICON_SIZES) {
    await generateIcon(size);
  }

  console.log('\n✓ All icons generated successfully!');
  console.log(`\nGenerated ${ICON_SIZES.length} icons in: ${OUTPUT_DIR}`);
}

// スクリプト実行
generateAllIcons().catch(error => {
  console.error('✗ Fatal error:', error);
  process.exit(1);
});
