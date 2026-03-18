const { test, expect, devices } = require("@playwright/test");

// const URL = "http://localhost:8000";
const URL = "https://mvgiaoxu.org";
const EMAIL = "son@gmail.com";
const PASSWORD = "0338300666";
const CLASS_ID = 45;

const iPhone = devices["iPhone 13"];

test.setTimeout(600000);

async function login(page) {
  await page.goto(`${URL}/login`, { timeout: 60000 });
  await page.fill('input[name="email"]', EMAIL);
  await page.fill('input[name="password"]', PASSWORD);
  await page.click('button[type="submit"]');
  await page.waitForURL(`${URL}/**`);
}

async function waitForLivewireIdle(page) {
  // Chờ Livewire load xong
  await page
    .waitForFunction(
      () => {
        return (
          typeof window.livewire !== "undefined" ||
          typeof window.Livewire !== "undefined"
        );
      },
      { timeout: 15000 },
    )
    .catch(() => {
      console.log("[warn] Livewire object không tìm thấy");
    });

  // Chờ Alpine x-data load xong — kiểm tra hasDraft có tồn tại không
  await page
    .waitForFunction(
      () => {
        const el = document.querySelector("[x-data]");
        if (!el) return false;
        try {
          // Kiểm tra Alpine đã khởi tạo component chưa
          return el._x_dataStack !== undefined;
        } catch (e) {
          return false;
        }
      },
      { timeout: 15000 },
    )
    .catch(() => {
      console.log("[warn] Alpine chưa khởi tạo xong");
    });

  await page.waitForTimeout(800);
}

// ==================== DESKTOP ====================

test("desktop - mo trang", async ({ page }) => {
  await login(page);
  await page.goto(`${URL}/attendance?classId=${CLASS_ID}`);
  await waitForLivewireIdle(page);

  await expect(page.locator("body")).not.toContainText("Whoops");
  await expect(page.locator("body")).not.toContainText("500");

  // Desktop phải thấy table
  await expect(page.locator("table").first()).toBeVisible();
});

test("desktop - luu diem danh", async ({ page }) => {
  await login(page);
  await page.goto(`${URL}/attendance?classId=${CLASS_ID}`);
  await waitForLivewireIdle(page);

  await page.waitForSelector("table");
  await page.locator('button[aria-label="Có mặt"]').first().click();
  await page.locator('button:has-text("Lưu điểm danh")').first().click();

  await expect(page.locator("text=Đã lưu")).toBeVisible({ timeout: 5000 });
});

// ==================== MOBILE ====================

test("mobile - mo trang", async ({ browser }) => {
  const ctx = await browser.newContext({
    ...iPhone,
    viewport: { width: 390, height: 844 },
  });
  const page = await ctx.newPage();

  page.on("console", (msg) =>
    console.log(`[browser ${msg.type()}] ${msg.text()}`),
  );
  page.on("pageerror", (err) =>
    console.error(`[browser error] ${err.message}`),
  );

  await login(page);
  console.log("[test] Đăng nhập xong");

  await page.goto(`${URL}/attendance?classId=${CLASS_ID}`);
  console.log("[test] Đã vào trang");

  // Log viewport
  const innerWidth = await page.evaluate(() => window.innerWidth);
  console.log(`[test] window.innerWidth = ${innerWidth}`);

  // Chờ Livewire sẵn sàng
  await page
    .waitForFunction(
      () => {
        return (
          typeof window.livewire !== "undefined" ||
          typeof window.Livewire !== "undefined"
        );
      },
      { timeout: 15000 },
    )
    .catch(() => {});

  // Emit lại viewModeDetected để Livewire biết đang ở mobile
  await page.evaluate(() => {
    const isMobile = window.innerWidth < 1024;
    console.log("[inject] emitting viewModeDetected, isMobile =", isMobile);

    // Livewire 2 dùng Livewire.emit
    if (window.Livewire) {
      window.Livewire.emit("viewModeDetected", isMobile ? "mobile" : "desktop");
    } else if (window.livewire) {
      window.livewire.emit("viewModeDetected", isMobile ? "mobile" : "desktop");
    }
  });
  console.log("[test] Đã emit viewModeDetected");

  // Chờ Livewire xử lý xong emit — re-render component
  await page.waitForTimeout(2000);
  await waitForLivewireIdle(page);

  // Log mobileSessionId sau khi emit
  const mobileSessionId = await page.evaluate(() => {
    // Tìm trong DOM xem session đã load chưa
    const dateButtons = document.querySelectorAll(
      'button[wire\\:click*="selectDate"]',
    );
    return `${dateButtons.length} date buttons found`;
  });
  console.log(`[test] ${mobileSessionId}`);

  // Assertions
  await expect(page.locator("body")).not.toContainText("Whoops");
  await expect(page.locator("body")).not.toContainText("500");

  await expect(
    page.locator('button[wire\\:click*="selectDate"]').first(),
  ).toBeVisible({ timeout: 15000 });
  console.log("[test] Date selector visible — mobile UI đúng");

  // Screenshot
  await page.screenshot({
    path: "tests/browser/screenshots/mobile-mo-trang.png",
    fullPage: true,
  });
  console.log("[test] Screenshot lưu xong");

  await ctx.close();
});

test("mobile - bam diem danh va luu", async ({ browser }) => {
  const ctx = await browser.newContext({
    ...iPhone,
    viewport: { width: 390, height: 844 },
  });
  const page = await ctx.newPage();

  page.on("console", (msg) =>
    console.log(`[browser ${msg.type()}] ${msg.text()}`),
  );
  page.on("pageerror", (err) =>
    console.error(`[browser error] ${err.message}`),
  );

  await login(page);
  console.log("[test] Đăng nhập xong");

  await page.goto(`${URL}/attendance?classId=${CLASS_ID}`);
  console.log("[test] Đã vào trang");

  // Emit mobile mode
  await page
    .waitForFunction(
      () =>
        typeof window.Livewire !== "undefined" ||
        typeof window.livewire !== "undefined",
      { timeout: 15000 },
    )
    .catch(() => console.log("[warn] Livewire không tìm thấy"));

  await page.evaluate(() => {
    const emit = window.Livewire || window.livewire;
    if (emit) emit.emit("viewModeDetected", "mobile");
  });
  console.log("[test] Đã emit viewModeDetected mobile");

  await page.waitForTimeout(2000);
  await waitForLivewireIdle(page);

  // Chờ danh sách học sinh hiện ra
  await page.waitForSelector('.lg\\:hidden button[aria-label="Có mặt"]', {
    timeout: 15000,
  });
  console.log("[test] Danh sách học sinh đã load");

  // Đếm số học sinh
  const studentCount = await page
    .locator('.lg\\:hidden button[aria-label="Có mặt"]')
    .count();
  console.log(`[test] Số học sinh = ${studentCount}`);

  // Tap có mặt cho học sinh đầu tiên
  await page.locator('.lg\\:hidden button[aria-label="Có mặt"]').first().tap();
  console.log("[test] Đã tap Có mặt học sinh 1");

  // Kiểm tra nút chuyển sang màu xanh
  await page.waitForTimeout(300);
  const isGreen = await page.evaluate(() => {
    const btn = document.querySelector(
      '.lg\\:hidden button[aria-label="Có mặt"]',
    );
    return btn?.classList.contains("bg-green-500");
  });
  console.log(`[test] Nút có mặt xanh = ${isGreen}`);

  // Tap vắng không phép cho học sinh thứ 2
  await page
    .locator('.lg\\:hidden button[aria-label="Vắng không phép"]')
    .nth(1)
    .tap();
  console.log("[test] Đã tap Vắng không phép học sinh 2");

  // Kiểm tra badge số thay đổi chưa lưu
  await page.waitForTimeout(300);
  const draftCount = await page
    .locator('button:has-text("Lưu điểm danh") span')
    .last()
    .textContent()
    .catch(() => "không thấy badge");
  console.log(`[test] Draft count badge = ${draftCount}`);

  // Bấm lưu — nút mobile ở bottom bar
  await page.locator('button:has-text("Lưu điểm danh")').last().tap();
  console.log("[test] Đã tap Lưu");

  // Chờ toast thành công
  await expect(page.locator("text=Đã lưu")).toBeVisible({ timeout: 8000 });
  console.log("[test] Toast Đã lưu xuất hiện");

  // Sau khi lưu — draft phải được xóa
  await page.waitForTimeout(500);
  const draftAfter = await page
    .locator('button:has-text("Lưu điểm danh") span')
    .last()
    .textContent()
    .catch(() => "0");
  console.log(`[test] Draft sau khi lưu = ${draftAfter}`);

  // Chụp screenshot
  await page.screenshot({
    path: "tests/browser/screenshots/mobile-sau-khi-luu.png",
    fullPage: true,
  });
  console.log("[test] Screenshot lưu xong");

  await ctx.close();
  console.log("[test] Xong");
});

test("mobile - 100 nguoi theo dot", async ({ browser }) => {
  const TOTAL = 100;
  const DOT_SIZE = 10; // mỗi đợt 10 người
  const results = [];

  for (let dot = 0; dot < TOTAL / DOT_SIZE; dot++) {
    console.log(`[test] Đợt ${dot + 1}/${TOTAL / DOT_SIZE}`);

    const contexts = await Promise.all(
      Array(DOT_SIZE)
        .fill(null)
        .map(() =>
          browser.newContext({
            ...iPhone,
            viewport: { width: 390, height: 844 },
          }),
        ),
    );

    const pages = await Promise.all(contexts.map((ctx) => ctx.newPage()));

    // Đăng nhập tuần tự
    for (const page of pages) {
      await login(page);
    }

    // Vào trang cùng lúc
    const dotResults = await Promise.all(
      pages.map(async (page, i) => {
        try {
          await page.goto(`${URL}/attendance?classId=${CLASS_ID}`, {
            timeout: 60000,
          });
          await waitForLivewireIdle(page);

          const has500 = await page
            .locator("text=500")
            .isVisible()
            .catch(() => false);

          const userNum = dot * DOT_SIZE + i + 1;
          console.log(`User ${userNum}: ${has500 ? "LỖI" : "OK"}`);

          return !has500;
        } catch (e) {
          const userNum = dot * DOT_SIZE + i + 1;
          console.log(`User ${userNum} lỗi: ${e.message}`);
          return false;
        }
      }),
    );

    results.push(...dotResults);

    // Đóng đợt này trước khi mở đợt tiếp
    await Promise.all(contexts.map((ctx) => ctx.close()));

    // Nghỉ 2 giây giữa các đợt
    await new Promise((r) => setTimeout(r, 5000));
  }

  // Tổng kết
  const passed = results.filter(Boolean).length;
  const failed = results.filter((r) => !r).length;
  console.log("================================");
  console.log(`[test] TỔNG: ${TOTAL} người`);
  console.log(`[test] PASS: ${passed}`);
  console.log(`[test] FAIL: ${failed}`);
  console.log("================================");

  expect(failed).toBe(0);
});

test("mobile - do thoi gian tai trang", async ({ browser }) => {
  const ctx = await browser.newContext({
    ...iPhone,
    viewport: { width: 390, height: 844 },
  });
  const page = await ctx.newPage();

  await login(page);
  console.log("[test] Đăng nhập xong");

  // Bắt đầu đo
  const start = Date.now();

  await page.goto(`${URL}/attendance?classId=${CLASS_ID}`);

  // Mốc 1: trang bắt đầu hiện
  const timeToFirstByte = Date.now() - start;
  console.log(`[perf] Time to first byte: ${timeToFirstByte}ms`);

  // Emit mobile
  await page
    .waitForFunction(
      () =>
        typeof window.Livewire !== "undefined" ||
        typeof window.livewire !== "undefined",
      { timeout: 15000 },
    )
    .catch(() => {});

  await page.evaluate(() => {
    const emit = window.Livewire || window.livewire;
    if (emit) emit.emit("viewModeDetected", "mobile");
  });

  // Mốc 2: chờ Livewire render xong
  await waitForLivewireIdle(page);
  const timeToLivewire = Date.now() - start;
  console.log(`[perf] Livewire idle: ${timeToLivewire}ms`);

  // Mốc 3: chờ date selector hiện — đây là lúc user thấy được UI
  await page
    .locator('button[wire\\:click*="selectDate"]')
    .first()
    .waitFor({ timeout: 15000 });
  const timeToInteractive = Date.now() - start;
  console.log(`[perf] UI sẵn sàng dùng: ${timeToInteractive}ms`);

  // Mốc 4: chờ danh sách học sinh hiện
  await page
    .locator('.lg\\:hidden button[aria-label="Có mặt"]')
    .first()
    .waitFor({ timeout: 15000 });
  const timeToStudents = Date.now() - start;
  console.log(`[perf] Danh sách học sinh hiện: ${timeToStudents}ms`);

  // Tổng kết
  console.log("================================");
  console.log(`[perf] TỔNG KẾT (mobile)`);
  console.log(`[perf] First byte:    ${timeToFirstByte}ms`);
  console.log(`[perf] Livewire idle: ${timeToLivewire}ms`);
  console.log(`[perf] UI tương tác:  ${timeToInteractive}ms`);
  console.log(`[perf] Học sinh load: ${timeToStudents}ms`);
  console.log("================================");

  // Ngưỡng chấp nhận được — mạng nhà thờ chậm nên để 5 giây
  expect(timeToInteractive).toBeLessThan(5000);
  expect(timeToStudents).toBeLessThan(8000);

  await page.screenshot({
    path: "tests/browser/screenshots/mobile-perf.png",
    fullPage: true,
  });

  await ctx.close();
});

test("mobile - thiet bi trung binh mang 4G", async ({ browser }) => {
  const ctx = await browser.newContext({
    ...iPhone,
    viewport: { width: 390, height: 844 },
  });
  const page = await ctx.newPage();

  // Giả lập thiết bị tầm trung
  await emulateSlowDevice(page);

  await login(page);

  const start = Date.now();
  await page.goto(`${URL}/attendance?classId=${CLASS_ID}`);

  await page
    .waitForFunction(
      () =>
        typeof window.Livewire !== "undefined" ||
        typeof window.livewire !== "undefined",
      { timeout: 30000 },
    )
    .catch(() => {});

  await page.evaluate(() => {
    const emit = window.Livewire || window.livewire;
    if (emit) emit.emit("viewModeDetected", "mobile");
  });

  await waitForLivewireIdle(page);
  const timeToLivewire = Date.now() - start;

  await page
    .locator('.lg\\:hidden button[aria-label="Có mặt"]')
    .first()
    .waitFor({ timeout: 30000 });
  const timeToStudents = Date.now() - start;

  console.log("================================");
  console.log("[perf] THIẾT BỊ TRUNG BÌNH - MẠNG 4G");
  console.log(`[perf] Livewire idle: ${timeToLivewire}ms`);
  console.log(`[perf] Học sinh load: ${timeToStudents}ms`);
  console.log("================================");

  // Ngưỡng cho thiết bị yếu — cho phép chậm hơn
  expect(timeToStudents).toBeLessThan(10000); // 10 giây

  await page.screenshot({
    path: "tests/browser/screenshots/mobile-slow-device.png",
    fullPage: true,
  });

  await ctx.close();
});
