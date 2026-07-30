import { test } from 'node:test';
import assert from 'node:assert/strict';

// Mock DOM elements and Web Component class logic corresponding to SalesRepRegionSelector
class MockElement {
  classList = new Set();
  attributes = {};
  focused = false;
  children = [];
  parentNode = null;

  setAttribute(k, v) { this.attributes[k] = String(v); }
  getAttribute(k) { return this.attributes[k] ?? null; }
  focus() { this.focused = true; }
  querySelector(sel) { return null; }
  querySelectorAll(sel) { return []; }
}

class MockRegionElement extends MockElement {
  dataset = {};
  constructor(regionId) {
    super();
    this.dataset.region = regionId;
  }
  classList = {
    classes: new Set(),
    add(...names) { names.forEach(n => this.classes.add(n)); },
    remove(...names) { names.forEach(n => this.classes.delete(n)); },
    contains(name) { return this.classes.has(name); }
  };
}

class MockSalesRepRegionSelector {
  isLocked = false;
  lockedRegionId = null;
  currentRepShown = null;
  regions = new Map();

  constructor() {
    ['panhandle', 'north', 'central', 'south'].forEach(id => {
      this.regions.set(id, new MockRegionElement(id));
    });
  }

  handleSelect(regionId) {
    if (this.isLocked && this.lockedRegionId === regionId) {
      const regionEl = this.regions.get(regionId);
      if (regionEl) regionEl.focus();
      return;
    }

    if (regionId) {
      this.lockRegion(regionId, this.regions.get(regionId));
    }
  }

  lockRegion(regionId, regionElement) {
    this.isLocked = true;
    this.lockedRegionId = regionId;
    this.showRep(regionId);
  }

  hoverRegion(regionId) {
    if (this.isLocked) return;
    this.showRep(regionId);
  }

  unhoverRegion() {
    if (this.isLocked) return;
    this.showRep(null);
  }

  unlockMap() {
    this.isLocked = false;
    this.lockedRegionId = null;
    this.showRep(null);
  }

  showRep(regionId) {
    this.currentRepShown = regionId;
  }
}

test('Sales Rep Region Selector - Permanently locks selected region until another region is clicked', () => {
  const component = new MockSalesRepRegionSelector();

  // Step 1: Initial state
  assert.equal(component.isLocked, false);
  assert.equal(component.lockedRegionId, null);

  // Step 2: Click North region -> Locks North
  component.handleSelect('north');
  assert.equal(component.isLocked, true);
  assert.equal(component.lockedRegionId, 'north');
  assert.equal(component.currentRepShown, 'north');

  // Step 3: Hover over Panhandle while North is locked -> Hover ignored, North stays shown
  component.hoverRegion('panhandle');
  assert.equal(component.isLocked, true);
  assert.equal(component.lockedRegionId, 'north');
  assert.equal(component.currentRepShown, 'north');

  // Step 4: Unhover mouse from map -> Selection stays locked to North
  component.unhoverRegion();
  assert.equal(component.isLocked, true);
  assert.equal(component.lockedRegionId, 'north');
  assert.equal(component.currentRepShown, 'north');

  // Step 5: Click North again -> Does NOT unlock, remains locked to North
  component.handleSelect('north');
  assert.equal(component.isLocked, true);
  assert.equal(component.lockedRegionId, 'north');
  assert.equal(component.currentRepShown, 'north');

  // Step 6: Click South region -> Updates lock to South
  component.handleSelect('south');
  assert.equal(component.isLocked, true);
  assert.equal(component.lockedRegionId, 'south');
  assert.equal(component.currentRepShown, 'south');

  // Step 7: Hover over Central while South is locked -> Hover ignored
  component.hoverRegion('central');
  assert.equal(component.currentRepShown, 'south');
});
