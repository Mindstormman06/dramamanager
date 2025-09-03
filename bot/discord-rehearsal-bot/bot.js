const { Client, GatewayIntentBits, EmbedBuilder } = require('discord.js');
require('dotenv').config();
const express = require('express');
const bodyParser = require('body-parser');
const fs = require('fs');
const path = require('path');

let GUILD_ID = process.env.GUILD_ID;
let SCHEDULE_PINGS = process.env.SCHEDULE_PINGS === 'true';
let SCHEDULE_CHANNEL_ID = process.env.SCHEDULE_CHANNEL_ID;
let CHANGELOG_PINGS = process.env.CHANGELOG_PINGS === 'true';
let CHANGELOG_CHANNEL_ID = process.env.CHANGELOG_CHANNEL_ID;

const reloadEnv = () => {
  require('dotenv').config();
  GUILD_ID = process.env.GUILD_ID;
  SCHEDULE_PINGS = process.env.SCHEDULE_PINGS === 'true';
  SCHEDULE_CHANNEL_ID = process.env.SCHEDULE_CHANNEL_ID;
  CHANGELOG_PINGS = process.env.CHANGELOG_PINGS === 'true';
  CHANGELOG_CHANNEL_ID = process.env.CHANGELOG_CHANNEL_ID;
  console.log('🔄 Reloaded .env');
};

fs.watchFile(path.join(__dirname, 'discord-rehearsal-bot/.env'), { interval: 1000 }, (curr, prev) => {
  reloadEnv();
});

const client = new Client({
  intents: [GatewayIntentBits.Guilds, GatewayIntentBits.GuildMembers]
});

client.once('ready', () => {
  console.log(`✅ Logged in as ${client.user.tag}`);
});

const app = express();
app.use(bodyParser.json());

// Rehearsal Scheduled
app.post('/rehearsal', async (req, res) => {
  const { title, date, notes, students } = req.body;

  try {
    const guild = await client.guilds.fetch(GUILD_ID);
    await guild.members.fetch();

  const participantsLines = students.map((s) => {
    if (!s.discord_username || s.discord_username === 'NODISCORD') {
      return `${s.first_name} ${s.last_name}`;
    }
    const member = guild.members.cache.find(m =>
      m.user.tag.toLowerCase() === s.discord_username.toLowerCase()
    );
    return member ? `<@${member.id}>` : `${s.first_name} ${s.last_name}`;
  });

  const mentionedIds = students
    .map((s) => {
      if (!s.discord_username || s.discord_username === 'NODISCORD') return null;
      const member = guild.members.cache.find(m =>
        m.user.tag.toLowerCase() === s.discord_username.toLowerCase()
      );
      return member ? `<@${member.id}>` : null;
    })
    .filter(Boolean);



    const embed = new EmbedBuilder()
      .setTitle('🎭 New Rehearsal Scheduled!')
      .setColor(0x0099FF00)
      .addFields(
        { name: 'Title', value: title, inline: false },
        { name: 'Date', value: date, inline: false },
        {
          name: 'Participants',
          value: participantsLines.length > 0 ? participantsLines.join('\n') : 'None',
          inline: false
        },
        {
          name: 'Notes',
          value: notes?.trim() ? notes.trim() : 'No additional notes.',
          inline: false
        }
      )
      .setTimestamp()
      .setFooter({ text: 'QSS Drama Scheduler' });

    if (SCHEDULE_PINGS && SCHEDULE_CHANNEL_ID) {
      const channel = await guild.channels.fetch(SCHEDULE_CHANNEL_ID);
      await channel.send({
        embeds: [embed],
        content: mentionedIds.join(' ') || null, // pings go here
        allowedMentions: {
            parse: ['users']
        }
    });
    }

    res.sendStatus(200);
  } catch (err) {
    console.error('❌ Error posting to Discord:', err);
    res.status(500).send('Failed to post embed');
  }
});

// Rehearsal Cancelled
app.post('/rehearsalcancel', async (req, res) => {
  const { title, date, notes, students } = req.body;

  try {
    const guild = await client.guilds.fetch(GUILD_ID);
    await guild.members.fetch();

    const participantsLines = students.map((s) => {
      if (!s.discord_username) {
        return `${s.first_name} ${s.last_name}`;
      }
      const member = guild.members.cache.find(m =>
        m.user.tag.toLowerCase() === s.discord_username.toLowerCase()
      );
      return member ? `<@${member.id}>` : `${s.first_name} ${s.last_name}`;
    });

    const mentionedIds = students
    .map((s) => {
        if (!s.discord_username) return null;
        const member = guild.members.cache.find(m =>
        m.user.tag.toLowerCase() === s.discord_username.toLowerCase()
        );
        return member ? `<@${member.id}>` : null;
    })
    .filter(Boolean); // remove nulls


    const embed = new EmbedBuilder()
      .setTitle('🎭 Rehearsal Cancelled!!')
      .setColor(0x7B1E3B)
      .addFields(
        { name: 'Title', value: title, inline: false },
        { name: 'Date', value: date, inline: false },
        {
          name: 'Participants',
          value: participantsLines.length > 0 ? participantsLines.join('\n') : 'None',
          inline: false
        },
        {
          name: 'Notes',
          value: notes?.trim() ? notes.trim() : 'No additional notes.',
          inline: false
        }
      )
      .setTimestamp()
      .setFooter({ text: 'QSS Drama Scheduler' });

    if (SCHEDULE_PINGS && SCHEDULE_CHANNEL_ID) {
      const channel = await guild.channels.fetch(SCHEDULE_CHANNEL_ID);
      await channel.send({
        embeds: [embed],
        content: mentionedIds.join(' ') || null, // pings go here
        allowedMentions: {
            parse: ['users']
        }
    });
    }

    res.sendStatus(200);
  } catch (err) {
    console.error('❌ Error posting to Discord:', err);
    res.status(500).send('Failed to post embed');
  }
});

// Changelog notification as a Discord embed
app.post('/changelog', async (req, res) => {
  const { title, description } = req.body;
  try {
    const guild = await client.guilds.fetch(GUILD_ID);
    if (CHANGELOG_PINGS && CHANGELOG_CHANNEL_ID) {
      const channel = await guild.channels.fetch(CHANGELOG_CHANNEL_ID);

      const embed = new EmbedBuilder()
        .setTitle('📝 Site Update')
        .setColor(0xFFD166)
        .addFields(
          { name: 'Version', value: title, inline: false },
          { name: 'Changelog', value: description, inline: false }
        )
        .setTimestamp()
        .setFooter({ text: 'QSS Drama Changelog' });

      await channel.send({ embeds: [embed] });
    }
    res.sendStatus(200);
  } catch (err) {
    console.error('❌ Error posting changelog:', err);
    res.status(500).send('Failed to post changelog');
  }
});

const PORT = 3079;
client.login(process.env.DISCORD_TOKEN);
app.listen(PORT, () => console.log(`📡 Bot server listening on port ${PORT}`));
