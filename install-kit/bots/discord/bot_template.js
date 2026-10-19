/**
 * Nyyo Docs Chatbot — Discord bot template (free install kit)
 * Node 18+: npm i discord.js
 * export DISCORD_BOT_TOKEN=... DISCORD_CLIENT_ID=...
 * node bot_template.js
 */
import { Client, GatewayIntentBits, REST, Routes, SlashCommandBuilder } from "discord.js";

const BOT_URL = process.env.NYYO_BOT_URL || "https://nyyoautomation.github.io/nyyo-docs-chatbot/";
const token = process.env.DISCORD_BOT_TOKEN;
const clientId = process.env.DISCORD_CLIENT_ID;
if (!token || !clientId) {
  console.error("Set DISCORD_BOT_TOKEN and DISCORD_CLIENT_ID");
  process.exit(1);
}

const commands = [
  new SlashCommandBuilder().setName("docs").setDescription("Open the Nyyo docs assistant"),
].map((c) => c.toJSON());

const rest = new REST({ version: "10" }).setToken(token);
await rest.put(Routes.applicationCommands(clientId), { body: commands });

const client = new Client({ intents: [GatewayIntentBits.Guilds] });
client.on("ready", () => console.log(`Logged in as ${client.user.tag}`));
client.on("interactionCreate", async (interaction) => {
  if (!interaction.isChatInputCommand()) return;
  if (interaction.commandName === "docs") {
    await interaction.reply({ content: `Docs assistant: ${BOT_URL}`, ephemeral: true });
  }
});
client.login(token);
