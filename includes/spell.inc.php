<?php

function SpellDescription($spell, $n, $csv = false) {
	global $dbspelleffects, $dbspelltargets, $dbskills, $dbstattypes, $items_table, $dbiracenames, $spells_table, $server_max_level, $negate_spell_bonuses;
	$is_charisma = $spell["effectid$n"] == 10;
	$is_charisma_two = $spell["effectid$n"] == 254;
	$has_charisma_value = ($is_charisma && abs($spell["effect_base_value$n"]) > 0);
	if (
		(!$is_charisma  && !$is_charisma_two) ||
		$has_charisma_value
	) {
		$print_buffer = "<tr>";
		$maxlvl = $spell["effect_base_value$n"];
		$minlvl = $server_max_level;
		for ($i = 1; $i <= 16; $i++) {
			if ($spell["classes" . $i] < $minlvl) {
				$minlvl = $spell["classes" . $i];
			}
		}
		$min = CalcSpellEffectValue(
			$spell["formula" . $n],
			$spell["effect_base_value$n"],
			$spell["max$n"],
			$minlvl
		);
		$max = CalcSpellEffectValue(
			$spell["formula" . $n],
			$spell["effect_base_value$n"],
			$spell["max$n"],
			$server_max_level
		);
		$base_limit = $spell["effect_limit_value$n"];
		if (($min < $max) && ($max < 0)) {
			$tn  = $min;
			$min = $max;
			$max = $tn;
		}
		if ($csv == true) {
			$print_buffer .= ",,";
		} else {
			$print_buffer .= "<td><b>Effect $n</b></td>";
		}
		switch ($spell["effectid$n"]) {
			case 3: // Increase Movement (% / 0)
				$base = $spell["effect_base_value$n"];
				$max = $spell["max$n"];				
				$print_buffer .= "<td>";
					if ($base < 0) { // Decrease
						$base = abs($base);
						$print_buffer .= "Decrease ";
					} else {
						$print_buffer .= "Increase ";
					}

					
					$name = $dbspelleffects[$spell["effectid$n"]];

					$print_buffer .= "$name by $base%";
					
					if ($max && $min != $max) {
						$max = abs($max);
						$print_buffer .= " to $max%";
					}
				$print_buffer .= "</td>";
				break;
			case 11: // Decrease OR Inscrease AttackSpeed (max/min = percentage of speed / normal speed, IE, 70=>-30% 130=>+30%
				$print_buffer .= "<td>";
					if ($max < 100) { // Decrease
						$print_buffer .= "Decrease Attack Speed";
						if ($min != $max) {
							$print_buffer .= " by " . (100 - $min) . "% to " . (100 - $max) . "%";
						} else {
							$print_buffer .= " by " . (100 - $max) . "%";
						}
					} else {
						$print_buffer .= "Increase Attack Speed";
						if ($min != $max) {
							$print_buffer .= " by " . ($min - 100) . "% to " . ($max - 100) . "%";
						} else {
							$print_buffer .= " by " . ($max - 100) . "%";
						}
					}
				$print_buffer .= "</td>";
				break;
			case 21: // Stun
				$print_buffer .= "<td>";
				$print_buffer .= $dbspelleffects[$spell["effectid$n"]];
				$min_seconds = ($min / 1000);
				$min_seconds_string = $min_seconds != 1 ? "s" : "";
				$print_buffer .= " for $min_seconds Second$min_seconds_string";
				if ($max && $min != $max) {
					$max_seconds = ($max / 1000);
					$max_seconds_string = $max_seconds != 1 ? "s" : "";
					$print_buffer .= " to $max_seconds_string Second";
				}
				$print_buffer .= "</td>";
				break;
			case 87: // Magnification
			case 98: // Haste v2
			case 119: // Haste v3
			case 123: // Spell Damage
			case 124: // Spell Damage
			case 125: // Spell Healing
			case 127: // Spell Haste
			case 128: // Spell Duration
			case 129: // Spell Range
			case 130: // Spell/Bash Hate
			case 131: // Chance of Using Reagent
			case 132: // Spell Mana Cost
			case 158: // Chance to Reflect Spell
			case 172: // Chance to Avoid Melee
			case 173: // Chance to Riposte
			case 174: // Chance to Dodge
			case 175: // Chance to Parry
			case 176: // Chance to Dual Wield
			case 177: // Chance to Double Attack
			case 180: // Chance to Resist Spell
			case 181: // Chance to Resist Fear Spell
			case 183: // All Skills Skill Check
			case 184: // Chance to Hit With all Skills
			case 185: // All Skills Damage Modifier
			case 186: // All Skills Minimum Damage Modifier
			case 188: // Chance to Block
			case 200: // Proc Modifier
			case 201: // Range Proc Modifier
			case 216: // Accuracy
			case 266: // Add Attack Chance
			case 273: // Critical Dot Chance
			case 294: // Critical Spell Chance
				$print_buffer .= "<td>";
					if ($min > 0) {
						$print_buffer .= "Increase ";
					} else {
						$print_buffer .= "Decrease ";
					}
					$print_buffer .= $dbspelleffects[$spell["effectid$n"]];

					if ($min != $max) {
						$min = number_format($min);
						$max = number_format($max);
						$print_buffer .= " by $min% to $max%";
					} else {
						$max = number_format($max);
						$print_buffer .= " by $max%";
					}
				$print_buffer .= "</td>";
				break;
			case 15: // Increase Mana per tick
			case 100: // Increase Hitpoints v2 per tick
				$print_buffer .= "<td>";
					if ($min > 0) {
						$print_buffer .= "Increase ";
					} else {
						$print_buffer .= "Decrease ";
					}

					$print_buffer .= $dbspelleffects[$spell["effectid$n"]];
					if ($min != $max) {
						$print_buffer .= " by " . number_format(abs($min)) . " to " . number_format(abs($max));
					} else {
						$print_buffer .= " by " . number_format(abs($max));
					}
				$print_buffer .= "</td>";
				break;
			case 30: // Frenzy Radius
				$radius = $spell["effect_base_value$n"];
				$max_level = $spell["max$n"];
				$print_buffer .= "<td>";
					$print_buffer .= "Decrease Frenzy Radius to $radius";
					if ($max_level) {
						$print_buffer .= " up to level $max_level";
					}
				$print_buffer .= "</td>";
				break;
			case 86: // Reaction Radius
				$radius = $spell["effect_base_value$n"];
				$max_level = $spell["max$n"];
				$print_buffer .= "<td>";
					$print_buffer .= "Decrease Reaction Radius to $radius";
					if ($max_level) {
						$print_buffer .= " up to level $max_level";
					}
				$print_buffer .= "</td>";
				break;
			case 22: // Charm
			case 23: // Fear
			case 31: // Mesmerize
				$effect_name = $dbspelleffects[$spell["effectid$n"]];
				$charm_max_level = $spell["max$n"];
				$charm_chance = $spell["effect_base_value$n"];

				$print_buffer .= "<td>";
					if ($charm_max_level) {
						$print_buffer .= "$effect_name up to Level $charm_max_level at a $charm_chance% chance";
					} else {						
						$print_buffer .= "$effect_name at a $charm_chance% chance";
					}
				$print_buffer .= "</td>";
				break;
			case 33: // Summon Pet:
			case 68: // Summon Skeleton Pet:
			case 71: // Summon Undead:
			case 106: // Summon Warder:
			case 108: // Summon Familiar:
			case 113: // Summon Horse:
			case 152: // Summon Pets:
				$print_buffer .= "<td>";
					$print_buffer .= $dbspelleffects[$spell["effectid$n"]];
					if ($csv == false) {
						$print_buffer .= " <a href='?a=pet&name=" . $spell["teleport_zone"] . "'>" . $spell["teleport_zone"] . "</a>";
					} else {
						$print_buffer .= ": " . $spell["teleport_zone"];
					}
				$print_buffer .= "</td>";
				break;
			case 12: // Invisibility
			case 13: // See Invisible
			case 14: // Water Breathing
			case 18: // Pacify
			case 20: // Blind
			case 25: // Bind Affinity
			case 26: // Gate
			case 28: // Invisibility versus Undead
			case 29: // Invisibility versus Animals
			case 40: // Invunerability
			case 41: // Destroy Target
			case 42: // Shadowstep
			case 44: // Lycanthropy
			case 52: // Sense Undead
			case 53: // Sense Summoned
			case 54: // Sense Animals
			case 56: // True North
			case 57: // Levitate
			case 61: // Identify
			case 64: // SpinStun
			case 65: // Infravision
			case 66: // UltraVision
			case 67: // Eye of Zomm
			case 68: // Reclaim Energy
			case 73: // Bind Sight
			case 74: // Feign Death
			case 75: // Voice Graft
			case 76: // Sentinel
			case 77: // Locate Corpse
			case 82: // Summon PC
			case 90: // Cloak
			case 93: // Stop Rain
			case 94: // Make Fragile (Delete if combat)
			case 95: // Sacrifice
			case 96: // Silence
			case 99: // Root
			case 101: // Complete Heal (with duration)
			case 103: // Call Pet
			case 104: // Translocate target to their bind point
			case 105: // Anti-Gate
			case 115: // Food/Water
			case 117: // Make Weapons Magical
			case 135: // Limit: Resist(Magic allowed)
			case 137: // Limit: Effect(Hitpoints allowed)
			case 138: // Limit: Spell Type(Detrimental only)
			case 141: // Limit: Instant spells only
			case 150: // Death Save - Restore Full Health
			case 151: // Suspend Pet - Lose Buffs and Equipment
			case 156: // Illusion: Target
			case 178: // Lifetap from Weapon Damage
			case 179: // Instrument Modifier
			case 182: // Hundred Hands Effect
			case 194: // Fade
			case 195: // Stun Resist
			case 205: // Rampage
			case 206: // Area of Effect Taunt
			case 299: // Wake the Dead
			case 311: // Limit: Combat Skills Not Allowed
			case 314: // Fixed Duration Invisbility
			case 315: // Fixed Duration Invisbility vs. Undead
				$print_buffer .= "<td>";
					$print_buffer .= $dbspelleffects[$spell["effectid$n"]];
				$print_buffer .= "</td>";
				break;
			case 380: // Knockback
				$push_up = $spell["effect_base_value$n"]; 	
				$push_back = $spell["effect_limit_value$n"];
				$print_buffer .= "<td>";
					$print_buffer .= "";
					if ($push_back) {
						if ($push_back < 1000) {
							$print_buffer .= "Knock up $push_up units and back $push_back units";
						} else {
							$chance = $push_up;
							$spell_name = get_spell_name($push_back);
							$print_buffer .= "Knockback triggers <a href = '?a=spell&id=$push_back'>$spell_name</a> at a $chance% chance";
						}
					} else {
						$print_buffer .= "Knock up $push_up units";
					}
				$print_buffer .= "</td>";
				break;
			case 58: // Illusion:
				$print_buffer .= "<td>";
					$print_buffer .= $dbspelleffects[$spell["effectid$n"]];
					$print_buffer .= $dbiracenames[$spell["effect_base_value$n"]];
				$print_buffer .= "</td>";
				break;
			case 63: // Memblur
				$effect_name = $dbspelleffects[$spell["effectid$n"]];
				$chance = $spell["effect_base_value$n"];			
				$max_level = $spell["max$n"];
				$print_buffer .= "<td>";
					if ($max_level) {
						$print_buffer .= "$effect_name up to Level $max_level at a $chance% chance";
					} else {						
						$print_buffer .= "$effect_name at a $chance% chance";
					}
				$print_buffer .= "</td>";
				break;
			case 120: // Set Healing Effectiveness
			case 330: // Critical Damage Mob
				$print_buffer .= "<td>";
					$print_buffer .= $dbspelleffects[$spell["effectid$n"]];
					$print_buffer .= " ($max%)";
				$print_buffer .= "</td>";
				break;
			case 81: // Resurrect
				$percent = $spell["effect_base_value$n"];
				$print_buffer .= "<td>";
					$print_buffer .= $dbspelleffects[$spell["effectid$n"]];
					if ($percent) {
						$print_buffer .= " and restore $percent% experience";
					}
				$print_buffer .= "</td>";
				break;
			case 83: // Teleport
			case 88: // Evacuate
			case 145: // Teleport v2
				$print_buffer .= "<td>";
					$print_buffer .= $dbspelleffects[$spell["effectid$n"]];
					if ($spell["teleport_zone"] != "" && $spell["teleport_zone"] != "same") {
						$print_buffer .= " to <a href=?a=zone&name=" . $spell["teleport_zone"] . ">" . get_zone_long_name($spell["teleport_zone"]) . "</a>";
					} else if ($spell["teleport_zone"] == "same") {
						$print_buffer .= " to Succor";
					}
				$print_buffer .= "</td>";
				break;
			case 289: // Improved Spell Effect:
				$print_buffer .= "<td>";
					$print_buffer .= $dbspelleffects[$spell["effectid$n"]];
					$name = get_field_result(
						"name",
						"SELECT name FROM $spells_table WHERE id=" . $spell["effect_base_value$n"]
					);
					$print_buffer .= "<a href=?a=spell&id=" . $spell["effect_base_value$n"] . ">$name</a>";
				$print_buffer .= "</td>";
				break;
			case 89: // Player Size
				$print_buffer .= "<td>";
					$name = $dbspelleffects[$spell["effectid$n"]];
					$min -= 100;
					$max -= 100;

					$prefix = "Increase";
					if ($min < 0) {
						$min = abs($min);
						$max = abs($max);
						$prefix = "Decrease";
					}
					
					$print_buffer .= "$prefix $name by $min%";
					if ($min != $max) {
						$print_buffer .= " to $max%";
					}
				$print_buffer .= "</td>";
				break;
			case 27: // Cancel Magic
				$modifier = $spell["effect_base_value$n"];
				$print_buffer .= "<td>";
					$print_buffer .= "Cancel Magic with a Level Modifier of $modifier";
				$print_buffer .= "</td>";
				break;
			case 134: // Limit: Maximum Level
			case 142: // Limit: Minimum Level
			case 157: // Spell-Damage Shield
				$print_buffer .= "<td>";
					$print_buffer .= $dbspelleffects[$spell["effectid$n"]];
					$print_buffer .= " ($max)";
				$print_buffer .= "</td>";
				break;
			case 121: // Reverse Damage Shield
				$damage = $spell["effect_base_value$n"];
				$name = $dbspelleffects[$spell["effectid$n"]];
				$prefix = "Decrease";
				if ($damage < 0) {
					$damage = abs($damage);
					$prefix = "Increase";
				}

				$print_buffer .= "<td>";
					$print_buffer .= "$prefix $name by $damage";
				$print_buffer .= "</td>";
				break;
			case 91: // Summon Corpse
				$print_buffer .= "<td>";
					$print_buffer .= $dbspelleffects[$spell["effectid$n"]];
					if ($max) {
						$print_buffer .= " up to Level $max";
					}
				$print_buffer .= "</td>";
				break;
			case 136: // Limit: Target
				$print_buffer .= "<td>";
					$print_buffer .= $dbspelleffects[$spell["effectid$n"]];
					if ($max < 0) {
						$max = -$max;
						$v = " Excluded";
					} else {
						$v = "";
					}
					$print_buffer .= " (" . $dbspelltargets[$max] . "$v)";
				$print_buffer .= "</td>";
				break;
			case 139: // Limit: Spell
				$print_buffer .= "<td>";
					$print_buffer .= $dbspelleffects[$spell["effectid$n"]];
					$max          = $spell["effect_base_value$n"];
					if ($max < 0) {
						$max = -$max;
						$v = " Excluded";
					}
					$name = get_field_result("name", "SELECT name FROM $spells_table WHERE id=$max");
					$print_buffer .= " (<a href=?a=spell&id=" . $spell["effect_base_value$n"] . ">$name</a>$v)";
				$print_buffer .= "</td>";
				break;
			case 140: // Limit: Min Duration
				$print_buffer .= "<td>";
					$print_buffer .= $dbspelleffects[$spell["effectid$n"]];
					$min *= 6;
					$max *= 6;
					if ($min != $max) {
						$print_buffer .= " ($min Second(s) to $max Second(s))";
					} else {
						$print_buffer .= " ($max Second(s))";
					}
				$print_buffer .= "</td>";
				break;
			case 143: // Limit: Min Casting Time
				$print_buffer .= "<td>";
					$print_buffer .= $dbspelleffects[$spell["effectid$n"]];
					$min          *= 6;
					$max          *= 6;
					if ($min != $max) {
						$print_buffer .= " (" . ($min / 6000) . " Second(s) to " . ($max / 6000) . " Second(s))";
					} else {
						$print_buffer .= " (" . ($max / 6000) . " Second(s))";
					}
				$print_buffer .= "</td>";
				break;
			case 148: // Stacking: Overwrite existing spell
			case 149: // Stacking: Overwrite existing spell
				$print_buffer .= "<td>";
					$print_buffer .= $dbspelleffects[$spell["effectid$n"]];
					$print_buffer .= " if slot " . ($spell["forumla$n"] - 201) . " is '" . $dbspelleffects[$spell["effect_base_value$n"]] . "' and less than " . $spell["effect_limit_value$n"];
				$print_buffer .= "</td>";
				break;
			case 147: // Percental Heal
				$percent = $spell["effect_base_value$n"];
				$max_heal = $spell["max$n"];
				$prefix = "Increase";
				if ($chance < 0) {
					$chance = abs($chance);
					$prefix = "Decrease";
				}

				$print_buffer .= "<td>";
					$name = $dbspelleffects[$spell["effectid$n"]];
					$print_buffer .= "$prefix $name by $percent%";
					if ($max_heal) {
						$max_heal = number_format($max_heal);
						$print_buffer .= " up to a max of $max_heal Health";
					}
				$print_buffer .= "</td>";
				break;
			case 153: // Balance Party Health
				$print_buffer .= "<td>";
					$print_buffer .= $dbspelleffects[$spell["effectid$n"]];
					$print_buffer .= " ($max% Penalty)";
				$print_buffer .= "</td>";
				break;
			case 193: // Skill Damage
				$skill_name = $dbskills[$spell["skill"]];
				$damage = $spell["effect_base_value$n"];
				$accuracy = $spell["effect_limit_value$n"];
				$print_buffer .= "<td>";
				if ($accuracy > 0) {
					$accuracy = number_format($accuracy);
					$print_buffer .= "Cause $damage $skill_name damage with a $accuracy% Accuracy modifier.";
				} else {
					$print_buffer .= "Cause $damage $skill_name damage.";
				}

				$print_buffer .= "</td>";
				break;
			case 418: // Skill Damage Increase
				$damage = $spell["effect_base_value$n"];
				if ($spell["effect_limit_value$n"] != -1) {
					$skill = $dbskills[$spell["effect_limit_value$n"]] != "" ? $dbskills[$spell["effect_limit_value$n"]] : "None";
				} else {
					$skill = "All Skills";
				}

				$print_buffer .= "<td>";
					$print_buffer .= "Increase damage for $skill by $damage.";
				$print_buffer .= "</td>";
				break;
			case 339: // Trigger on Cast
			case 340: // Spell Trigger
			case 374: // Apply Effect
			case 383: // Sympathetic Proc
				$spell_id = $spell["effect_limit_value$n"];
				$spell_chance = $spell["effect_base_value$n"] ;
				$spell_name = get_spell_name($spell_id);
				$print_buffer .= "<td>";
					$print_buffer .= "Triggers <a href = '?a=spell&id=$spell_id'>$spell_name</a> at a $spell_chance% chance";
				$print_buffer .= "</td>";
				break;
			case 351: // Aura
				$print_buffer .= "<td>";
					$print_buffer .= "Add Aura";
				$print_buffer .= "</td>";
				break;
			case 348: // Limit Minimum Mana
				$minimum_mana = $spell["effect_base_value$n"];
				$print_buffer .= "<td>";
					$print_buffer .= "Limit: Minimum Mana ($minimum_mana)";
				$print_buffer .= "</td>";
				break;
			case 296: // Focus Spell Vulnerability
				$min_vulnerability = $spell["effect_base_value$n"];
				$max_vulnerability = $spell["effect_limit_value$n"];

				$print_buffer .= "<td>";
					if ($min_vulnerability > 0) {
						$print_buffer .= "Increase ";
					} else {
						$print_buffer .= "Decrease ";
					}

					$print_buffer .= "Spell Vulnerability by $min_vulnerability% to $max_vulnerability%";
				$print_buffer .= "</td>";
				break;
			case 334: // Bard AE DOT
				$dot_damage = $spell["effect_base_value$n"];
				$print_buffer .= "<td>";
					if ($dot_damage < 0) {
						$dot_damage = abs($dot_damage);
						$print_buffer .= "Cause $dot_damage Damage";
					} else {
						$print_buffer .= "Heal for $dot_damage Damage";
					}
				$print_buffer .= "</td>";
				break;
			case 189: // Current Endurance
				$min = $spell["effect_base_value$n"];
				$max = $spell["max$n"];
				$print_buffer .= "<td>";
					if ($min < 0) {
						$damage = abs($min);
						$print_buffer .= "Decrease ";
					} else {
						$print_buffer .= "Increase ";
					}

					$print_buffer .= "Endurance by $min";

					if ($max) {
						$print_buffer .= " to $max";
					}

				$print_buffer .= "</td>";
				break;
			case 161: // Mitigate Spell Damage
				$mitigation_percent = $spell["effect_base_value$n"];
				$max_damage = number_format($spell["max$n"]);
				$print_buffer .= "<td>";
					$print_buffer .= "Mitigates Spell Damage by $mitigation_percent% until $max_damage damage is taken";
				$print_buffer .= "</td>";
				break;
			case 162: // Mitigate Melee Damage
				$mitigation_percent = $spell["effect_base_value$n"];
				$max_damage = number_format($spell["max$n"]);
				$print_buffer .= "<td>";
					$print_buffer .= "Mitigates Melee Damage by $mitigation_percent% until $max_damage damage is taken";
				$print_buffer .= "</td>";
				break;
			case 114: // Aggro Multiplier
				$percent = $spell["effect_base_value$n"];
				$print_buffer .= "<td>";
					if ($percent < 0) {
						$percent = abs($percent);
						$print_buffer .= "Decrease ";
					} else {
						$print_buffer .= "Increase ";
					}
					$print_buffer .= "Aggro Multiplier by $percent%";
				$print_buffer .= "</td>";
				break;
			case 298: // Change Size
				$size = $spell["effect_base_value$n"];
				$print_buffer .= "<td>";
					$print_buffer .= "Change Size to $size%";
				$print_buffer .= "</td>";
				break;
			case 85: // Add Proc
				$spell_id = $spell["effect_base_value$n"];
				$spell_chance = $spell["effect_limit_value$n"] ;
				$spell_name = get_spell_name($spell_id);
				if ($spell_id) {
					$print_buffer .= "<td>";
						if ($spell_chance) {
							$print_buffer .= "Add <a href = '?a=spell&id=$spell_id'>$spell_name</a> Proc with a $spell_chance% Proc rate modifier";
						} else {
							$print_buffer .= "Add <a href = '?a=spell&id=$spell_id'>$spell_name</a> Proc";
						}
					$print_buffer .= "</td>";
				}
				break;
			case 323: // Add Defensive Proc:
				$spell_id = $spell["effect_base_value$n"];
				$spell_chance = $spell["effect_limit_value$n"] ;
				$spell_name = get_spell_name($spell_id);
				if ($spell_id) {
					$print_buffer .= "<td>";
					if ($spell_chance) {
						$print_buffer .= "Add <a href = '?a=spell&id=$spell_id'>$spell_name</a> Defensive Proc with a $spell_chance% Proc rate modifier";
					} else {
						$print_buffer .= "Add <a href = '?a=spell&id=$spell_id'>$spell_name</a> Defensive Proc";
					}
					$print_buffer .= "</td>";
				}
				break;
			case 163: // Negate Attacks
				$attacks = $spell["effect_base_value$n"];
				$count = $attacks != 1 ? "s" : "";
				$print_buffer .= "<td>";
					$print_buffer .= "Negate $attacks Attack$count";
				$print_buffer .= "</td>";
				break;
			case 0: // HP
			case 1: // AC
			case 2: // ATK
			case 4: // STR
			case 5: // DEX
			case 6: // AGI
			case 7: // STA
			case 8: // INT
			case 9: // WIS
			case 35: // Disease Counter
			case 36: // Poison Counter
			case 46: // Magic Fire
			case 47: // Magic Cold
			case 48: // Magic Poison
			case 49: // Magic Disease
			case 50: // Magic Resist
			case 69: // Increase Max Hitpoints
			case 79: // HP Once
			case 116: // Curse Counter
				$base = $spell["effect_base_value$n"];
				$max = $spell["max$n"];
				$print_buffer .= "<td>";
					if ($base < 0) {
						$base = abs($base);
						$print_buffer .= "Decrease ";
					} else {
						$print_buffer .= "Increase ";
					}

					$min_string = number_format($base);
					$print_buffer .= $dbspelleffects[$spell["effectid$n"]] . " by $min_string";

					if ($max && $max != $base) {
						$max = number_format($max);
						$print_buffer .= " to $max";
					}

				$print_buffer .= "</td>";
				break;
			case 168: // Melee Mitigation
				$chance = $spell["effect_base_value$n"];
				$prefix = "Decrease";
				if ($chance < 0) {
					$chance = abs($chance);
					$prefix = "Increase";
				}

				$chance = number_format($chance);
				$print_buffer .= "<td>";
					$print_buffer .= "$prefix Melee Mitigation by $chance%";
				$print_buffer .= "</td>";
				break;
			case 171: // Increase Crippling Blow Chance
				$chance = $spell["effect_base_value$n"];
				$prefix = "Increase";
				if ($chance < 0) {
					$chance = abs($chance);
					$prefix = "Decrease";
				}

				$chance = number_format($chance);
				$print_buffer .= "<td>";
					$print_buffer .= "$prefix Crippling Blow Chance by $chance%";
				$print_buffer .= "</td>";
				break;
			case 164: // Appraise LDoN Trap
				$chance = $spell["max$n"];
				$print_buffer .= "<td>";
					$print_buffer .= "Appraise traps at a $chance% Chance";
				$print_buffer .= "</td>";
				break;
			case 165: // Disarm LDoN Trap
				$chance = $spell["max$n"];
				$print_buffer .= "<td>";
					$print_buffer .= "Disarm traps at a $chance% Chance";
				$print_buffer .= "</td>";
				break;
			case 166: // Unlock LDoN Trap
				$chance = $spell["max$n"];
				$print_buffer .= "<td>";
					$print_buffer .= "Unlock traps at a $chance% Chance";
				$print_buffer .= "</td>";
				break;
			case 437: // Teleport to Anchor
			case 438: // Translocate to Anchor
				$print_buffer .= "<td>";
					$print_buffer .= "Send to Anchor";
				$print_buffer .= "</td>";
				break;				
			case 59: // Damage Shield
				$base = $spell["effect_base_value$n"];
				$max = $spell["max$n"];
				$print_buffer .= "<td>";
					if ($base < 0) {
						$base = abs($base);
						$print_buffer .= "Increase ";
					} else {
						$print_buffer .= "Decrease ";
					}

					$min_string = number_format($base);
					$print_buffer .= $dbspelleffects[$spell["effectid$n"]] . " by $min_string";

					if ($max && $max != $base) {
						$max = number_format($max);
						$print_buffer .= " to $max";
					}

				$print_buffer .= "</td>";
				break;
			case 84: // Toss Up
				$damage = $spell["effect_base_value$n"];
				$prefix = "increase";
				if ($damage < 0) {
					$damage = abs($damage);
					$prefix = "decrease";
				}
				
				$print_buffer .= "<td>";
					$print_buffer .= "Throw and $prefix Health by $damage";
				$print_buffer .= "</td>";
				break;
			case 197: // Skill Damage Taken
				$percent = $spell["effect_base_value$n"];
				if ($spell["effect_limit_value$n"] != -1) {
					$skill = $dbskills[$spell["effect_limit_value$n"]] != "" ? $dbskills[$spell["effect_limit_value$n"]] : "None";
				} else {
					$skill = "All Skills";
				}

				$prefix = "Decrease";
				if ($damage < 0) {
					$damage = abs($damage);
					$prefix = "Increase";
				}
				
				$print_buffer .= "<td>";
					$print_buffer .= "$prefix $skill Damage Taken by $percent%";
				$print_buffer .= "</td>";
				break;
			case 169: // Chance to Critical Hit
				$percent = $spell["effect_base_value$n"];
				if ($spell["effect_limit_value$n"] != -1) {
					$skill = $dbskills[$spell["effect_limit_value$n"]] != "" ? $dbskills[$spell["effect_limit_value$n"]] : "None";
				} else {
					$skill = "All Skills";
				}

				$prefix = "Increase";
				if ($damage < 0) {
					$damage = abs($damage);
					$prefix = "Decrease";
				}
				
				$print_buffer .= "<td>";
					$print_buffer .= "$prefix $skill Critical Chance by $percent%";
				$print_buffer .= "</td>";
				break;
			case 262: // Stat Cap
				$base_cap = $spell["effect_base_value$n"];
				$max_cap = $spell["max$n"];
				$stat_type = $dbstattypes[$spell["effect_limit_value$n"]];

				$prefix = "Increase";
				if ($base_cap < 0) {
					$base_cap = abs($base_cap);
					$prefix = "Decrease";
				}

				$print_buffer .= "<td>";
					$print_buffer .= "$prefix Stat Cap for $stat_type by $base_cap";

					if ($max_cap && $max_cap != $base_cap) {
						$max_cap = number_format($max_cap);
						$print_buffer .= " to $max_cap";
					}
				$print_buffer .= "</td>";

				break;
			case 154: // Dispel Detrimental
				$chance = ($spell["effect_base_value$n"] / 10);
				$print_buffer .= "<td>";
					$print_buffer .= "Dispel Detrimental Spells at a $chance% chance";
				$print_buffer .= "</td>";
				break;
			case 209: // Dispel Beneficial
				$chance = ($spell["effect_base_value$n"] / 10);
				$print_buffer .= "<td>";
					$print_buffer .= "Dispel Beneficial Spells at a $chance% chance";
				$print_buffer .= "</td>";
				break;
			case 382: // Negate Spell Effect
				$spell_id = $spell["effect_base_value"];
				$negate_type = $negate_spell_bonuses[$spell["effect_base_value$n"]];
				$negate_effect = $dbspelleffects[$spell["effect_limit_value$n"]];
				$print_buffer .= "<td>";
					$print_buffer .= "Negate $negate_type for $negate_effect";
				$print_buffer .= "</td>";
				break;
			case 32: // Summon Item
			case 109: // Summon Bagged Item
				$item_id = $spell["effect_base_value$n"];
				$print_buffer .= "<td>";
					$print_buffer .= $dbspelleffects[$spell["effectid$n"]];
					$name = get_item_name($item_id);
					$icon = get_item_icon_from_id($item_id);
					if ($name != "") {
						$print_buffer .= ": <a href='?a=item&id=$item_id'>$icon $name</a><br>";
					}
				$print_buffer .= "</td>";
				break;				
			case 227: // Reduce Skill Timer
				$percent = $spell["effect_base_value$n"];
				if ($spell["skill"] != -1) {
					$skill = $dbskills[$spell["skill"]] != "" ? $dbskills[$spell["skill"]] : "None";
				} else {
					$skill = "All Skills";
				}

				$prefix = "Decrease";
				if ($damage < 0) {
					$damage = abs($damage);
					$prefix = "Increase";
				}
				
				$print_buffer .= "<td>";
					$print_buffer .= "$prefix $skill Skill Timer by $percent%";
				$print_buffer .= "</td>";
				break;
			case 219: // Slay Undead
				$rate_modifier = number_format($spell["effect_base_value$n"]);
				$damage_modifier = number_format($spell["effect_limit_value$n"]);
				
				$print_buffer .= "<td>";
					$print_buffer .= "Slay Undead with a $rate_modifier% chance modifier and $damage_modifier% damage modifier";
				$print_buffer .= "</td>";
				break;
			case 335: // Block Next Spell Focus
				$chance = number_format($spell["effect_base_value$n"]);				
				$print_buffer .= "<td>";
					$print_buffer .= "Block Next Spell at a $chance% chance";
				$print_buffer .= "</td>";
				break;
			case 320: // Shield Block
				$chance = number_format($spell["effect_base_value$n"]);			

				$prefix = "Increase";
				if ($chance < 0) {
					$chance = abs($chance);
					$prefix = "Decrease";
				}

				$print_buffer .= "<td>";
					$print_buffer .= "$prefix Shield Block chance by $chance%";
				$print_buffer .= "</td>";
				break;
			case 373: // Cast on Fade
				$spell_id = $spell["effect_base_value$n"];
				$spell_name = get_spell_name($spell_id);
				$print_buffer .= "<td>";
					$print_buffer .= "Triggers <a href = '?a=spell&id=$spell_id'>$spell_name</a> on fade";
				$print_buffer .= "</td>";
				break;
			case 286: // Spell Flat Damage Caster
				$damage = $spell["effect_base_value$n"];

				$prefix = "Increase";
				if ($damage < 0) {
					$damage = abs($damage);
					$prefix = "Decrease";
				}

				$damage = number_format($damage);

				$print_buffer .= "<td>";
					$print_buffer .= "$prefix Caster's Spell Damage by $damage";
				$print_buffer .= "</td>";
				break;
			case 297: // Spell Flat Damage Target
				$damage = $spell["effect_base_value$n"];

				$prefix = "Increase";
				if ($damage < 0) {
					$damage = abs($damage);
					$prefix = "Decrease";
				}

				$damage = number_format($damage);

				$print_buffer .= "<td>";
					$print_buffer .= "$prefix Target's Spell Damage taken by $damage";
				$print_buffer .= "</td>";
				break;
			case 411: // Limit Class
				$classes = $spell["effect_base_value$n"];
				$class_string = get_class_usable_string($classes);

				$print_buffer .= "<td>";
					$print_buffer .= "Limit: Classes ($class_string)";
				$print_buffer .= "</td>";
				break;
			case 414: // Limit Casting Skill
				$skill_id = $spell["effect_base_value$n"];
				$skill = $dbskills[$skill_id];
				
				$print_buffer .= "<td>";
					if ($skill_id >= 0) {
						$print_buffer .= "Limit: Skill ($skill)";
					} else {
						$print_buffer .= "Limit: Skill (Exclude $skill)";
					}
				$print_buffer .= "</td>";
				break;
			case 19: // Faction
			case 55: // Absorb Damage
			case 78: // Absorb Magic Damage
			case 92: // Gate
			case 97: // Mana Pool
			case 111: // All Resists
			case 112: // Effective Casting
			case 118: // Singing Skill
			case 159: // Stats
			case 167: // Pet Power Increase
			case 192: // Hate
			default:
				$print_buffer .= "<td>";
					$name = $dbspelleffects[$spell["effectid$n"]];
					$prefix = "Increase";
					if ($max < 0) {
						$prefix = "Decrease";
					}
					$print_buffer .= "$prefix $name";
					if ($min != $max) {
						$min = number_format($min);
						$print_buffer .= " by $min to $max";
					} else {
						if ($max < 0) {
							$max = -$max;
						}
						$max = number_format($max);
						$print_buffer .= " by $max";
					}
				$print_buffer .= "</td>";
				break;
		}
		$print_buffer .= "</td></tr>";
		return $print_buffer;
	}

	return "";
}
